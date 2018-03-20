<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\constants\Constants;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\WorkQueueController;
use app\modules\v2\controllers\ActivityController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Inspection;
use app\modules\v2\models\Event;
use app\modules\v2\models\Asset;
use app\modules\v2\models\WorkOrder;
use app\modules\v2\models\WorkQueue;
use app\modules\v2\models\WebManagementInspectionsByMapGrid;
use app\modules\v2\models\WebManagementInspectionsByMapGridSectionNumber;
use app\modules\v2\models\WebManagementInspectionsInspections;
use app\modules\v2\models\WebManagementInspectionsEvents;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class InspectionController extends Controller 
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'update' => ['put'],
					'clear-event' => ['put'],
					'get-map-grids' => ['get'],
					'get-inspections' => ['get'],
					'get-inspection-events' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public static function processInspection($data, $client, $activityID)
	{
		try
		{
			//set client header
			BaseActiveRecord::setClient($client);
			
			$inspectionCount = count($data);
			$responseArray = [];
			
			//try catch to log individual errors
			try
			{	
				$inspectionSuccessFlag = 0;
				$eventResponse = [];
				$assetResponse = (object)[];
				$completeWorkResponse = [
					'WorkQueue' => (object)[],
					'WorkOrder' => (object)[]
				];
				$inspectionID = null;
			
				$newInspection = new Inspection;
				$newInspection->attributes = $data;
				$newInspection->ActivityID = $activityID;
				
				//check if Inspection already exist.
				$previousInspection = Inspection::find()
					->where(['InspectionTabletID' => $newInspection->InspectionTabletID])
					//->andWhere(['DeletedFlag' => 0]) no flag exist currently
					->one();
				//TODO move work queue completion to after event processing?
				if ($previousInspection == null) {
					if ($newInspection->save()) {
						$inspectionSuccessFlag = 1;
						$inspectionID = $newInspection->ID;
						//if inspection is not ad hoc handle work queue/work order updates
						if($newInspection->IsAdHocFlag == 0)
							$completeWorkResponse = self::completeWork($data);
					} else {
						throw BaseActiveController::modelValidationException($newInspection);
					}
				}
				else
				{
					//send success if Inspection record was already saved previously
					$inspectionSuccessFlag = 1;
					$inspectionID = $previousInspection->ID;
					$completeWorkResponse = self::completeWork($data);
				}
				//process event data if available
				if(array_key_exists('Event', $data))
				{
					if($data['Event'] != null)
						$eventResponse = self::processEvent($data['Event'], $client, $inspectionID);
				}
				if(array_key_exists('Asset', $data))
				{
					if($data['Asset'] != null)
						$assetResponse = self::processAsset($data['Asset'], $client, $inspectionID);
					if (array_key_exists('ID', $assetResponse))
					{
						//create ad hoc work queue
						if($data['IsAdHocFlag'] == 1)
						{
							$completeWorkResponse = WorkQueueController::createAdHocWorkQueue($assetResponse['ID'], $data['CreatedBy'], $data['CreatedDate'], $client);
							//add new work queue id to inspection record.
							$newInspection->WorkQueueID = $completeWorkResponse['WorkQueue']->WorkQueueID;
						}
						//if previous inspection exist asset id will already be set
						if($previousInspection == null)
						{
							//add asset ID to inspection record
							$newInspection->AssetID = $assetResponse['ID'];
							if(!$newInspection->update())
							{
								throw BaseActiveController::modelValidationException($newInspection);
							}
						}
					}
				}
				$responseArray = [
					'ID' => $inspectionID,
					'InspectionTabletID' => $newInspection->InspectionTabletID,
					'SuccessFlag' => $inspectionSuccessFlag,
					'WorkQueue' => $completeWorkResponse['WorkQueue'],
					'WorkOrder' => $completeWorkResponse['WorkOrder'],
					'Event' => $eventResponse,
					'Asset' => $assetResponse];
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
				$responseArray = [
					'ID' => $inspectionID,
					'InspectionTabletID' => $data['InspectionTabletID'],
					'SuccessFlag' => $inspectionSuccessFlag,
					'WorkQueue' => $completeWorkResponse['WorkQueue'],
					'WorkOrder' => $completeWorkResponse['WorkOrder'],
					'Event' => $eventResponse,
					'Asset' => $assetResponse];
			}
			//return response data
			return $responseArray;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	private static function processEvent($data, $client, $inspectionID)
	{
		//set client header
		BaseActiveRecord::setClient($client);
		$eventCount = count($data);
		$eventResponse = [];		
		//traverse Event array
		for($i = 0; $i < $eventCount; $i++)
		{
			//try catch to log individual errors
			try
			{	
				$eventSuccessFlag = 0;
				$eventID = null;
				//get dynamic event model
				$eventModel = BaseActiveRecord::getEventModel($client);
				
				//check if Event already exist.
				$previousEvent = $eventModel::find()
					->where(['EventTabletID' => $data[$i]['EventTabletID']])
					//->andWhere(['DeletedFlag' => 0]) no flag exist currently
					->one();
					
				if ($previousEvent == null) {
					$newEvent = new $eventModel;
					$newEvent->attributes = $data[$i];
					$newEvent->InspectionID = $inspectionID;
					//set created by if null, current issue with tablet data 1/30/18
					$newEvent->CreatedByUserID = ($newEvent->CreatedByUserID == null ? $data[$i]['CreatedUserID'] : $newEvent->CreatedByUserID);
					if ($newEvent->save()) {
						$eventSuccessFlag = 1;
						$eventID = $newEvent->ID;
					} else {
						throw BaseActiveController::modelValidationException($newEvent);
					}
				}
				elseif($_SERVER['REQUEST_METHOD'] === 'PUT')
				{
					//Handle updates if applicable.
					$previousEvent->attributes = $data[$i];
					if($previousEvent->update())
					{
						$eventSuccessFlag = 1;
						$eventID = $previousEvent->ID;
					} else {
						throw BaseActiveController::modelValidationException($previousEvent);
					}
				}
				else
				{
					//send success if Event record was already saved previously
					$eventSuccessFlag = 1;
					$eventID = $previousEvent->ID;
				}
				$eventResponse[] = ['ID' => $eventID, 'EventTabletID' => $data[$i]['EventTabletID'],'SuccessFlag' => $eventSuccessFlag];
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i]);
				$eventResponse[] = ['EventTabletID' => $data[$i]['EventTabletID'],'SuccessFlag' => $eventSuccessFlag];
			}
		}
		return $eventResponse;
	}
	
	private static function processAsset($data, $client, $inspectionID)
	{		
		//try catch to log errors
		try
		{	
			//set client header
			BaseActiveRecord::setClient($client);
			$assetResponse = [];
			$assetSuccessFlag = 0;
			$assetID = null;
		
			$assetModel = BaseActiveRecord::getAssetModel($client);
		
			//check if Asset already exist.
			$previousAsset = $assetModel::find()
				->where(['AssetTabletID' => $data['AssetTabletID']])
				//->andWhere(['DeletedFlag' => 0]) no flag exist currently
				->one();
				
			if ($previousAsset == null) {
				$newAsset = new $assetModel;
				$newAsset->attributes = $data;
				$newAsset->InspectionID = $inspectionID;
				if ($newAsset->save()) {
					$assetSuccessFlag = 1;
					$assetID = $newAsset->ID;
				} else {
					throw BaseActiveController::modelValidationException($newAsset);
				}
			}
			elseif($_SERVER['REQUEST_METHOD'] === 'PUT')
			{
				//Handle updates if applicable.
				$previousAsset->attributes = $data;
				if($previousAsset->update())
				{
					$assetSuccessFlag = 1;
					$assetID = $previousAsset->ID;
				} else {
					throw BaseActiveController::modelValidationException($previousAsset);
				}
			}
			else
			{
				//send success if Asset record was already saved previously
				$assetSuccessFlag = 1;
				$assetID = $previousAsset->ID;
			}
			$assetResponse = ['ID' => $assetID, 'AssetTabletID' => $data['AssetTabletID'],'SuccessFlag' => $assetSuccessFlag];
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
			$assetResponse = ['AssetTabletID' => $data['AssetTabletID'],'SuccessFlag' => $assetSuccessFlag];
		}
		return $assetResponse;
	}
	
	//used by completeWork function to check for completed work queues for given array or work queues
	private static function hasCompletedWorkQueue($workQueueArray){
		$workQueueCount = count($workQueueArray);
		for($i = 0; $i < $workQueueCount; $i++)
		{
			if($workQueueArray[$i]['WorkQueueStatus'] == Constants::WORK_QUEUE_COMPLETED)
				return true;
		}
		return false;
	}
	
	//used by completeWork and actionUpdate to get work order based on inspection workqueueid
	private static function getWorkOrderByWorkQueue($workQueueID)
	{
		BaseActiveRecord::setClient(getallheaders()['X-Client']);
		//build sub query
		$subQuery = WorkQueue::find()
			->select('WorkOrderID')
			->where(['ID' => $workQueueID]);
		//get work order via subQuery
		$workOrder = WorkOrder::find()
			->where(['ID' => $subQuery])
			->one();
		return $workOrder;
	}
	
	    //used by updateEventIndicator to get all non deleted events for a given work order
    private static function getAllEventsByWorkOrder($workOrder)
    {
        //get all events from workOrder 
        $query = Event::find()
            ->innerJoin('tInspection', 'tEvent.InspectionID = tInspection.ID')
            ->innerJoin('tWorkQueue', 'tInspection.WorkQueueID = tWorkQueue.ID')
            ->innerJoin('tWorkOrder', 'tWorkQueue.WorkOrderID = tWorkOrder.ID')
            ->where(['tWorkOrder.ID' => $workOrder->ID])
            ->andWhere(['tEvent.DeletedFlag' => 0]);
        $eventArray = $query->asArray()->all();
        
        return $eventArray;
    }

	
	//used by complete work to set work queue status to 102 completed
	private static function updateWorkQueue($inspectionData)
	{
		try{
			$workQueue = WorkQueue::find()
				->where(['ID' => $inspectionData['WorkQueueID']])
				->one();
			if($workQueue != null && $workQueue->WorkQueueStatus != Constants::WORK_QUEUE_COMPLETED)
			{
				$workQueue->WorkQueueStatus = Constants::WORK_QUEUE_COMPLETED;
				$workQueue->ModifiedBy = $inspectionData['CreatedBy'];
				$workQueue->ModifiedDate =  $inspectionData['CreatedDate'];
				if($workQueue->update())
				{
					return true;
				}
				else
				{
					throw BaseActiveController::modelValidationException($workQueue);
				}
			}	
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
		}
		return false;
	}
	
	//used by actionUpdate to update work order event indicator based on new inspection data
	private static function updateEventIndicator($inspectionData, $workOrder)
	{
		try{
			$workOrder->ModifiedBy = $inspectionData['CreatedBy'];
			$workOrder->ModifiedDateTime = $inspectionData['CreatedDate'];
			//if update is a cge set it to a cge regardless
			if($inspectionData['IsCGEFlag'] ==1)
			{
				$workOrder->EventIndicator =  Constants::WORK_ORDER_CGE;
				$workOrder->CompletedDate = null;
				$workOrder->CompletedFlag = 0;
			}
			//get all events for this work order that are not marked deleted if result > 0 set as with event else set as no event
			elseif(count(self::getAllEventsByWorkOrder($workOrder)) > 0)
			{
				$workOrder->EventIndicator = Constants::WORK_ORDER_COMPLETED_WITH_EVENT;
			}
			else
			{
				//get all events for this work order that are not marked completed
				$workOrder->EventIndicator = Constants::WORK_ORDER_COMPLETED_NO_EVENT;
			}
			//update
			if($workOrder->update())
			{
				return [
					'ID' =>$workOrder->ID,
					'SuccessFlag' => 1
				];
			}
			else
			{
				throw BaseActiveController::modelValidationException($workOrder);
			}
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
		}
		return  [
			'ID' => $workOrder->ID,
			'SuccessFlag' => 0
		];
	}
	
	//used by completeWork to update work orders according to given params
	private static function updateWorkOrder($inspectionData, $workOrder, $updateEventIndicator = false, $updateInspectionAttemptCounter = false, $setComplete = false)
	{
		try{
			$workOrder->ModifiedBy = $inspectionData['CreatedBy'];
			$workOrder->ModifiedDateTime = $inspectionData['CreatedDate'];
			if($updateEventIndicator)
			{
				//have to check if it is set to 1 previously because value default on db is NULL
				if($workOrder->EventIndicator !== Constants::WORK_ORDER_COMPLETED_WITH_EVENT)
				{
					if(array_key_exists('Event', $inspectionData) && count($inspectionData['Event']) > 0)
					{
						$workOrder->EventIndicator = Constants::WORK_ORDER_COMPLETED_WITH_EVENT;
					}
					else
					{
						$workOrder->EventIndicator = Constants::WORK_ORDER_COMPLETED_NO_EVENT;
					}
				}
			}
			if($updateInspectionAttemptCounter)
			{
				$workOrder->InspectionAttemptCounter =  $workOrder->InspectionAttemptCounter + 1;
			}
			if($setComplete)
			{
				$workOrder->CompletedDate = $inspectionData['CreatedDate'];
				$workOrder->CompletedFlag = 1;
			}
			//update
			if($workOrder->update())
			{
				return true;
			}
			else
			{
				throw BaseActiveController::modelValidationException($workOrder);
			}
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
		}
		return false;
	}
	
	private static function completeWork($inspectionData)
	{
		try
		{
			//create response format
			$responseData = [];
			$workQueueStatus = Constants::WORK_QUEUE_IN_PROGRESS;
			$workQueueSuccessFlag = 0;
			$workOrderID = null;
			$workOrderSuccessFlag = 0;
			//try catch to log individual errors
			try
			{
				//get workOrder by work queue id
				$workOrder = self::getWorkOrderByWorkQueue($inspectionData['WorkQueueID']);
				// check if work order was found and not completed yet
				if($workOrder !== null && $workOrder->CompletedFlag != 1)
				{
					//get all work queues for work order
					$workQueueArray = WorkQueue::find()
						->where(['WorkOrderID' => $workOrder->ID])
						->all();
					//set work order id for response
					$workOrderID = $workOrder->ID;
					if(count($workQueueArray) > 1) {
						// check workqueue statuses
						if($inspectionData['IsCGEFlag'] == 0 && !self::hasCompletedWorkQueue($workQueueArray)) {
							// close work queue
							if(self::updateWorkQueue($inspectionData))
							{
								$workQueueSuccessFlag = 1;
								$workQueueStatus = Constants::WORK_QUEUE_COMPLETED;
								// set event indicator
								if(self::updateWorkOrder($inspectionData, $workOrder, true)) $workOrderSuccessFlag = 1;
							}
						} else if($inspectionData['IsCGEFlag'] == 0 && self::hasCompletedWorkQueue($workQueueArray)) {
							// close work queue 
							if(self::updateWorkQueue($inspectionData))
							{
								$workQueueSuccessFlag = 1;
								$workQueueStatus = Constants::WORK_QUEUE_COMPLETED;
								//if attempt counter is 1 can override previous CGE
								if($workOrder->InspectionAttemptCounter === 1)
								{
									// set event indicator, increment work order attempt counter ,close work order
									if(self::updateWorkOrder($inspectionData, $workOrder, true, true, true)) $workOrderSuccessFlag = 1;
								}
								//if attempt counter is 0 cannot override previous CGE
								elseif($workOrder->InspectionAttemptCounter === 0)
								{
									if($workOrder->EventIndicator === 2)
									{
										// increment work order attempt counter
										//if(self::updateWorkOrder($inspectionData, $workOrder, false, true)) $workOrderSuccessFlag = 1;
										$workOrderSuccessFlag = 1;
									} else {
										// set event indicator, increment work order attempt counter, close work order
										if(self::updateWorkOrder($inspectionData, $workOrder, true, true, true)) $workOrderSuccessFlag = 1;
									}
								}
							}
						} else if($inspectionData['IsCGEFlag'] == 1) {
							//if is CGE doing nothing regardless of completed state of other work queues will be handled by task out SP
							Yii::trace("I'm doing nothing because the inspection is a CGE!!!");
						}
					//else if if is not a CGE, will attempt to complete work queue if that returns success handle work order
					} elseif($inspectionData['IsCGEFlag'] !== 1 && self::updateWorkQueue($inspectionData)) {
						$workQueueSuccessFlag = 1;
						$workQueueStatus = Constants::WORK_QUEUE_COMPLETED;
						$eventIndicator = 0;
						if(array_key_exists('IsAdHocFlag', $inspectionData) && $inspectionData['IsAdHocFlag'] == 1)
						{
							$eventIndicator = 3;
						}
						elseif(array_key_exists('Event', $inspectionData) && count($inspectionData['Event']) > 0)
						{
							$eventIndicator = 1;
						}
						//assign new data
						$workOrder->EventIndicator = $eventIndicator;
						$workOrder->CompletedFlag = 1;
						$workOrder->CompletedDate = $inspectionData['CreatedDate'];
						$workOrder->ModifiedBy = $inspectionData['CreatedBy'];
						$workOrder->ModifiedDateTime = $inspectionData['CreatedDate'];
						$workOrder->InspectionAttemptCounter =  $workOrder->InspectionAttemptCounter + 1;
						//update
						if($workOrder->update())
						{
							$workOrderSuccessFlag = 1;
						}
						else
						{
							throw BaseActiveController::modelValidationException($workOrder);
						}
					}elseif($inspectionData['IsCGEFlag'] == 1){
						$workQueueSuccessFlag = 1;
						$workOrderSuccessFlag = 1;
					}
				}
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $inspectionData);//update this inserted data value
			}
			$responseData = [
				'WorkQueue' => (object)['WorkQueueID' => $inspectionData['WorkQueueID'],
										'WorkQueueStatus' => $workQueueStatus,
										'SuccessFlag' => $workQueueSuccessFlag],
				'WorkOrder' => (object)['WorkOrderID' => $workOrderID,
										'SuccessFlag' => $workOrderSuccessFlag]
			];
			return $responseData;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionUpdate()
	{
		try
		{			
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true);
			
			//archive json data
			BaseActiveController::archiveJson($body, 'InspectionUpdate', BaseActiveController::getClientUser($client)->UserID, $client);
			
			$inspectionData = $data['activity'][0]['Inspection'];
			
			//create response format
			$responseData = [];
			
			//try catch to log individual errors
			try
			{	
				$successFlag = 0;
				if(array_key_exists('ID' ,$inspectionData))
				{
					$inspectionID = $inspectionData['ID'];
					$inspection = Inspection::find()
						->where(['ID' => $inspectionID])
						->one();
					if($inspection != null)
					{
						$inspection->attributes = $inspectionData;
						if($inspection->update())
						{
							$successFlag = 1;
							unset($data['activity'][0]['Inspection']);
							$responseData = ActivityController::actionCreate($data)->data;
							//build base response
							$responseData['activity'][0]['Inspection'] = [
								'ID' => $inspection->ID,
								'InspectionTabletID' => $inspection->InspectionTabletID,
								'SuccessFlag' => $successFlag
							];
							//process event data if available
							if(array_key_exists('Event', $inspectionData) && $inspectionData['Event'] != null)
							{
								$responseData['activity'][0]['Inspection']['Event'] = self::processEvent($inspectionData['Event'], $client, $inspectionID);
							}
							//process asset data if available
							if(array_key_exists('Asset', $inspectionData) && $inspectionData['Asset'] != null)
								$responseData['activity'][0]['Inspection']['Asset'] = self::processAsset($inspectionData['Asset'], $client, $inspectionID);
							//get workOrder by work queue id
							$workOrder = self::getWorkOrderByWorkQueue($inspectionData['WorkQueueID']);
							//update event indicator
							if($workOrder->EventIndicator != Constants::WORK_ORDER_CGE)
							{
								$responseData['activity'][0]['Inspection']['WorkOrder'] = self::updateEventIndicator($inspectionData, $workOrder);
							}
							else
							{
								$responseData['activity'][0]['Inspection']['WorkOrder'] = [
									'ID' =>$workOrder->ID,
									'SuccessFlag' => 1
								];
							}
						}
						else
						{
							throw BaseActiveController::modelValidationException($inspection);
						}
					}
					else{
						$responseData = ActivityController::actionCreate($data)->data;
					}
				}
				else
				{
					$responseData = ActivityController::actionCreate($data)->data;
				}
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $inspectionData);
			}			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionClearEvent()
	{
		try
		{
			//set db
			$headers = getallheaders();
			$client =  $headers['X-Client'];
			BaseActiveRecord::setClient($client);
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true);
			
			//archive json data
			BaseActiveController::archiveJson($body, 'ClearEvent', BaseActiveController::getClientUser($client)->UserID, $client);
			
			//create response format
			$responseData = [];
			
			//count number of items to delete
			$deletedRecords = $data['Event'];
			$deletedCount = count($deletedRecords);
			
			//loop records to be marked deleted
			for($i = 0; $i < $deletedCount; $i++)
			{
				//try catch to log individual errors
				try
				{	
					$successFlag = 0; 
					//get dynamic event model
					$eventModel = BaseActiveRecord::getEventModel($client);
					$event = $eventModel::find()
						->where(['ID' => $deletedRecords[$i]['ID']])
						->andWhere(['<>', 'DeletedFlag', 1])
						->one();
					if($event != null)
					{
						$event->DeletedFlag = 1;
						if($event->update())
						{
							$successFlag = 1;
						}
						else
						{
							throw BaseActiveController::modelValidationException($event);
						}
					}
					else{
						$successFlag = 1;
					}
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $deletedRecords[$i]);
				}
				$responseData['Event'][] = ['ID' => $deletedRecords[$i]['ID'], 'SuccessFlag' => $successFlag];
			}
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGetMapGrids($mapGridSelected = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//get headers
			$headers = getallheaders();
			
			//set db
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			
			if($mapGridSelected != null)
			{
				$orderBy = 'SectionNumber';
				$envelope = 'sections';
				$assetQuery = WebManagementInspectionsByMapGridSectionNumber::find()
					->where(['MapGrid' => $mapGridSelected]);
			}
			else
			{
				$orderBy = 'ComplianceEnd';
				$envelope = 'mapGrids';
				$assetQuery = WebManagementInspectionsByMapGrid::find();
				
				if($filter != null)
				{
					$assetQuery->andFilterWhere([
					'or',
					['like', 'MapGrid', $filter],
					['like', 'ComplianceStart', $filter],
					['like', 'ComplianceEnd', $filter],
					['like', 'TotalInspections', $filter],
					['like', 'PercentageComplete', $filter],
					]);
				}
			}
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$data = $paginationResponse['Query']->orderBy($orderBy)
				->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray[$envelope] = $data;
			}
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGetInspections($mapGridSelected = null, $sectionNumberSelected = null, $inspectionID = null, $workOrderID = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//get headers
			$headers = getallheaders();
			
			//set db
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			$assetQuery = '';
			
			if($workOrderID != null)
			{
				$inspections = WebManagementInspectionsInspections::find()
					->where(['WorkOrderID' => $workOrderID])
					->orderBy('InspectionDateTime')
					->all();
				$responseArray['inspections'] = $inspections;
			}
			else
			{
				if($inspectionID != null)
				{
					$orderBy = 'EventType';
					$envelope = 'events';
					$assetQuery = WebManagementInspectionsEvents::find()
						->where(['InspectionID' => $inspectionID]);
				}
				else
				{
					if($sectionNumberSelected)
					{
						$assetQuery = WebManagementInspectionsInspections::find()
							->where(['MapGrid' => $mapGridSelected])
							->andWhere(['SectionNumber' => $sectionNumberSelected]);
					}
					else
					{
						$assetQuery = WebManagementInspectionsInspections::find()
						->where(['MapGrid' => $mapGridSelected]);
					}
					$orderBy = 'InspectionDateTime';
					$envelope = 'inspections';
					if($filter != null)
					{
						$assetQuery->andFilterWhere([
						'or',
						['like', 'MapGrid', $filter],
						['like', 'SectionNumber', $filter],
						['like', 'Inspector', $filter],
						['like', 'InspectionDateTime', $filter],
						['like', 'InspectionLatutude', $filter],
						['like', 'InspectionLongitude', $filter],
						]);
					}
				}
				
				if($page != null && $assetQuery != '')
				{
					//pass query with pagination data to helper method
					$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
					//use updated query with pagination caluse to get data
					$data = $paginationResponse['Query']->orderBy($orderBy)
					->all();
					$responseArray['pages'] = $paginationResponse['pages'];
					$responseArray[$envelope] = $data;
				}
			}
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGetInspectionEvents($workOrderID, $inspectionID)
	{
		try
		{
			//get headers
			$headers = getallheaders();
			
			//set db
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$connection = BaseActiveRecord::getDb();
			$getEventsCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spMapViewDetails :WorkOrderID,:InspectionID");
			$getEventsCommand->bindParam(':WorkOrderID', $workOrderID,  \PDO::PARAM_INT);
			$getEventsCommand->bindParam(':InspectionID', $inspectionID,  \PDO::PARAM_INT);
			$results['events'] = $getEventsCommand->query();
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $results;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
}