<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
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
				$workQueueResponse = (object)[];
				$workOrderResponse = (object)[];
				$inspectionID = null;
			
				$newInspection = new Inspection;
				$newInspection->attributes = $data;
				$newInspection->ActivityID = $activityID;
				
				//check if Inspection already exist.
				$previousInspection = Inspection::find()
					->where(['InspectionTabletID' => $newInspection->InspectionTabletID])
					//->andWhere(['DeletedFlag' => 0]) no flag exist currently
					->one();

				if ($previousInspection == null) {
					if ($newInspection->save()) {
						$inspectionSuccessFlag = 1;
						$inspectionID = $newInspection->ID;
						//set associate work queue to completed (WorkQueueStatus  = 102)
						$workQueueResponse = WorkQueueController::complete($data['WorkQueueID'], $data['WorkQueueStatus'], $client, $data['CreatedBy'], $data['CreatedDate']);
						$workOrderResponse = self::completeWorkOrder($data);
					} else {
						throw BaseActiveController::modelValidationException($newInspection);
					}
				}
				else
				{
					//Handle updates if applicable.
					//send success if Inspection record was already saved previously
					$inspectionSuccessFlag = 1;
					$inspectionID = $previousInspection->ID;
					//set associate work queue to completed (WorkQueueStatus  = 102)
					$workQueueResponse = WorkQueueController::complete($data['WorkQueueID'], $data['WorkQueueStatus'], $client, $data['CreatedBy'], $data['CreatedDate']);
					$workOrderResponse = self::completeWorkOrder($data);
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
							$workQueueResponse = WorkQueueController::createAdHocWorkQueue($assetResponse['ID'], $data['CreatedBy'], $data['CreatedDate'], $client);
							//add new work queue id to inspection record.
							$newInspection->WorkQueueID = $workQueueResponse['WorkQueueID'];
						}
						//add asset ID to inspection record
						$newInspection->AssetID = $assetResponse['ID'];
						if(!$newInspection->update())
							throw BaseActiveController::modelValidationException($newInspection);
						
					}
				}
				$responseArray = [
					'ID' => $inspectionID,
					'InspectionTabletID' => $newInspection->InspectionTabletID,
					'SuccessFlag' => $inspectionSuccessFlag,
					'WorkQueue' => $workQueueResponse,
					'WorkOrder' => $workOrderResponse,
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
					'WorkQueue' => $workQueueResponse,
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
		
			//check if Asset already exist.
			$previousAsset = Asset::find()
				->where(['AssetTabletID' => $data['AssetTabletID']])
				//->andWhere(['DeletedFlag' => 0]) no flag exist currently
				->one();
				
			if ($previousAsset == null) {
				$newAsset = new Asset;
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
	
	private static function completeWorkOrder($inspectionData)
	{
		try
		{
			//create response format
			$responseData = [];
			//try catch to log individual errors
			try
			{
				$successFlag = 0;
				$workOrderID = null;
				//query to get work queue workOrderID if completed
				$workQueue = WorkQueue::find()
					->select('WorkOrderID')
					//get id from inspection
					->where(['ID' => $inspectionData['WorkQueueID']])
					->andWhere(['WorkQueueStatus' => WorkQueueController::$completed])
					->one();
				//check if completed work queue was found
				if($workQueue != null)
				{
					//get work order id from work queue
					$workOrderID = $workQueue->WorkOrderID;
					//count all work queues
					$totalWorkQueueCount = WorkQueue::find()
						->where(['WorkOrderID' => $workOrderID])
						->count();
					//get work order
					$workOrder = WorkOrder::find()
						->where(['ID' => $workOrderID])
						->one();
					//check if work order was found and not completed yet
					if($workOrder != null && $workOrder->CompletedFlag != 1)
					{
						//skip if record is cge SP handles these during task out to prevent attempt counter errrors
						if(!array_key_exists('IsCGEFlag', $inspectionData) || $inspectionData['IsCGEFlag'] != 1)
						{
							//check work queue count
							if($totalWorkQueueCount > 1)
							//handle for records with multiple work queues
							{
								//count all completed work queues associated with this work order
								$completedWorkQueueCount = WorkQueue::find()
									->where(['WorkOrderID' => $workOrderID])
									->andWhere(['WorkQueueStatus' => WorkQueueController::$completed])
									->count();
								//handle appropriate updates to work order record
								$eventIndicator = $workOrder->EventIndicator;
								/*////////////////////////////////////
								if EI 2 && IACount 0
									successFlag = 1
								else
									if EI != 1
										override EI
									update EI
									if total == completed 
										complete WO
										increment IA
									update WO
										successFlag = 1
								////////////////////////////////////*/
								//if record is EI of 2 and IA of 0 indicates a Dual Dispatach CGE, in which case no cation will be taken
								if($eventIndicator === 2 && $workOrder->InspectionAttemptCounter == 0)
								{
									$successFlag = 1;
								}
								else
								{
									//IE != 1 override IE value with new value
									if($eventIndicator != 1)
									{
										if(array_key_exists('Event', $inspectionData) && count($inspectionData['Event']) > 0)
										{
											$eventIndicator = 1;
										}
										else
										{
											$eventIndicator = 0;
										}
									}
									//assign new data
									$workOrder->EventIndicator = $eventIndicator;
									$workOrder->ModifiedBy = $inspectionData['CreatedBy'];
									$workOrder->ModifiedDateTime = $inspectionData['CreatedDate'];
									//only mark work order as completed and increment counter if this is the only open record.
									if($totalWorkQueueCount === $completedWorkQueueCount)
									{
										$workOrder->CompletedFlag = 1;
										$workOrder->CompletedDate = $inspectionData['CreatedDate'];
										$workOrder->InspectionAttemptCounter =  $workOrder->InspectionAttemptCounter + 1;
									}
									//update
									if($workOrder->update())
									{
										$successFlag = 1;
									}
									else
									{
										throw BaseActiveController::modelValidationException($workOrder);
									}
								}
							}
							//handle for records without multiple work queues
							else
							{
								//handle appropriate updates to work order record
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
									$successFlag = 1;
								}
								else
								{
									throw BaseActiveController::modelValidationException($workOrder);
								}
							}
						}
						else
						{
							$successFlag = 1;
						}
					}	
				}
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $inspectionData);//update this inserted data value
			}
			$responseData = [
				'WorkOrderID' => $workOrderID,
				'SuccessFlag' => $successFlag
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
								$responseData['activity'][0]['Inspection']['Event'] = self::processEvent($inspectionData['Event'], $client, $inspectionID);
							//process asset data if available
							if(array_key_exists('Asset', $inspectionData) && $inspectionData['Asset'] != null)
								$responseData['activity'][0]['Inspection']['Asset'] = self::processAsset($inspectionData['Asset'], $client, $inspectionID);
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