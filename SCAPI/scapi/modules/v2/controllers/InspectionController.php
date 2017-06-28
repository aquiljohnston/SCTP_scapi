<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\WorkQueueController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Inspection;
use app\modules\v2\models\Event;
use app\modules\v2\models\Asset;
use app\modules\v2\models\WorkOrder;
use app\modules\v2\models\WorkQueue;
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
					//'create' => ['post'],
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
				$assetResponse = [];
				$workQueueResponse = [];
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
						$eventResponse = self::processEvent($data['Event'], $client, $activityID, $inspectionID);
				}
				if(array_key_exists('Asset', $data))
				{
					if($data['Asset'] != null)
						$assetResponse = self::processAsset($data['Asset'], $client, $activityID, $inspectionID);
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
				$responseArray[] = [
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
	
	private static function processEvent($data, $client, $activityID, $inspectionID)
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
			
				$newEvent = new Event;
				$newEvent->attributes = $data[$i];
				$newEvent->InspectionID = $inspectionID;
				
				//check if Event already exist.
				$previousEvent = Event::find()
					->where(['EventTabletID' => $newEvent->EventTabletID])
					//->andWhere(['DeletedFlag' => 0]) no flag exist currently
					->one();
					
				if ($previousEvent == null) {
					if ($newEvent->save()) {
						$eventSuccessFlag = 1;
						$eventID = $newEvent->ID;
					} else {
						throw BaseActiveController::modelValidationException($newEvent);
					}
				}
				else
				{
					//Handle updates if applicable.
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
	
	private static function processAsset($data, $client, $activityID, $inspectionID)
	{		
		//try catch to log errors
		try
		{	
			//set client header
			BaseActiveRecord::setClient($client);
			$assetResponse = [];
			$assetSuccessFlag = 0;
			$assetID = null;
		
			$newAsset = new Asset;
			$newAsset->attributes = $data;
			$newAsset->InspectionID = $inspectionID;
			
			//check if Asset already exist.
			$previousAsset = Asset::find()
				->where(['AssetTabletID' => $newAsset->AssetTabletID])
				//->andWhere(['DeletedFlag' => 0]) no flag exist currently
				->one();
				
			if ($previousAsset == null) {
				if ($newAsset->save()) {
					$assetSuccessFlag = 1;
					$assetID = $newAsset->ID;
				} else {
					throw BaseActiveController::modelValidationException($newAsset);
				}
			}
			else
			{
				//Handle updates if applicable.
				//send success if Asset record was already saved previously
				$assetSuccessFlag = 1;
				$assetID = $previousAsset->ID;
			}
			$assetResponse[] = ['ID' => $assetID, 'AssetTabletID' => $data['AssetTabletID'],'SuccessFlag' => $assetSuccessFlag];
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
			$assetResponse[] = ['AssetTabletID' => $data['AssetTabletID'],'SuccessFlag' => $assetSuccessFlag];
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
				$workOrderID = '';
				$workQueue = WorkQueue::find()
					->where(['ID' => $inspectionData['WorkQueueID']])
					->andWhere(['WorkQueueStatus' => WorkQueueController::$completed])
					->one();
				if($workQueue != null)
				{
					$workOrderID = $workQueue->WorkOrderID;
					$workOrder = WorkOrder::find()
						->where(['ID' => $workQueue->WorkOrderID])
						->one();
					if($workOrder != null)
					{
						//record error if record was already completed
						if($workOrder->CompletedFlag == 1)
						{
							BaseActiveController::archiveErrorJson(file_get_contents("php://input"), new \yii\web\HttpException(500), getallheaders()['X-Client'], $workOrderID);
						}
						else
						{
							if($inspectionData['IsCGEFlag'] != 1)
							{
								//handle appropriate updates to work order record
								$completedData = $inspectionData['CreatedDate'];
								$eventIndicator = 0;
								$completedFlag = 0;
								$inspectionAttemptCounter = $workOrder->InspectionAttemptCounter + 1; 
								if($inspectionData['IsAOCFlag'] == 1 || $inspectionData['IsAdHocFlag'] == 1 || $inspectionData['IsIndicationFlag'] == 1)
								{
									if($inspectionData['IsAdHocFlag'])
									{
										$eventIndicator = 3;
										$completedFlag = 1;
									}
									else
									{
										$eventIndicator = 1;
										$completedFlag = 1;
									}
								}
								else
								{
									$eventIndicator = 0;
									$completedFlag = 1;
								}
								//assign new data
								$workOrder->EventIndicator = $eventIndicator;
								$workOrder->CompletedFlag = $completedFlag;
								$workOrder->CompletedDate = $completedData;
								$workOrder->ModifiedBy = $inspectionData['CreatedBy'];
								$workOrder->ModifiedDateTime = $completedData;
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
							else
							{
								$successFlag = 1;
							}
						}
					}	
				}
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $workQueueID);//update this inserted data value
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
}