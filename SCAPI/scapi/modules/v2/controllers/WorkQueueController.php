<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\constants\Constants;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\AssignedWorkQueue;
use app\modules\v2\models\WorkQueue;
use app\modules\v2\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Connection;

class WorkQueueController extends Controller 
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
					'get' => ['get'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGet($userID)
	{
		try{
			ini_set('memory_limit', '-1');
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('workQueueGet', $client);
			
			$responseArray = [];
			$responseArray['WorkQueue'] = AssignedWorkQueue::find()
				->select('WorkQueueID
				,WorkOrderID
				,InspectionType
				,HouseNumber
				,Street
				,AptSuite
				,City
				,State
				,Zip
				,MeterNumber
				,MeterLocationDesc
				,LocationType
				,LocationLatitude
				,LocationLongitude
				,MapGrid
				,ComplianceStart
				,ComplianceEnd
				,MapLatitudeBegin
				,MapLongitudeBegin
				,MapLatitudeEnd
				,MapLongitudeEnd
				,AccountNumber
				,AccountName
				,AccountTelephoneNumber
				,Comments
				,SequenceNumber
				,SectionNumber
				,WorkQueueStatus
				,BillingCode
				,MeterLocation
				,PipelineFootage
				,SpecialInstructions
				,AssignedToID
				,AttemptCounter
				,ScheduledDispatchDate
				,CGEReason')
				->where(['AssignedToID' => $userID])
				->all();
					
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
            throw new \yii\web\HttpException(400);
        }
	}
	
	//fuction called by activity to parse and accept work queues
	public static function accept($data, $client){
		try{
			//set db
			BaseActiveRecord::setClient($client);
			
			//get requesting userID
			$modifiedBy = BaseActiveController::getClientUser($client)->UserID;
			$modifiedUsername = BaseActiveController::getClientUser($client)->UserName;
			
			//create response format
			$responseData = [];
			
			//count number of items to update
			$workQueueCount = count($data);
			
			//counts for accepted and complete work
			$acceptedCount = 0;
			$completedCount = 0;
			
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			//process accepted
			for($i = 0; $i < $workQueueCount; $i++){
				//try catch to log individual errors
				try{
					$workQueue = (object)[];
					$successFlag = 0;
					$workQueue = WorkQueue::find()
						->where(['ID' => $data[$i]['WorkQueueID']])
						->andWhere(['not in', 'WorkQueueStatus', [Constants::WORK_QUEUE_COMPLETED]])
						->one();
					if($workQueue != null){
						$workQueue->WorkQueueStatus = $data[$i]['WorkQueueStatus'];
						$workQueue->ModifiedBy = $modifiedBy;
						$workQueue->ModifiedDate = $data[$i]['ModifiedDate'];
						if($workQueue->update()){
							$successFlag = 1;
							//increment counts for logging
							if($workQueue->WorkQueueStatus == 101) $acceptedCount++;
							if($workQueue->WorkQueueStatus == 102) $completedCount++;
						}else{
							throw BaseActiveController::modelValidationException($workQueue);
						}
					}else{
						$successFlag = 0;
					}
				} catch(\Exception $e) {
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i], json_encode($workQueue));
				}
				$responseData[] = [
					'WorkQueueID' => $data[$i]['WorkQueueID'],
					'SuccessFlag' => $successFlag
				];
			}
			//log counts
			$logString = 'Total Work Queues: ' . $workQueueCount . ', Accepted Work Queues: ' . $acceptedCount . ', Completed Work Queues: ' . $completedCount;
			BaseActiveController::archiveJson($logString, 'WorkQueuesProcessed ', $modifiedUsername, $client);
			
			$transaction->commit();
			return $responseData;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
            throw new \yii\web\HttpException(400);
        }
	}
	
	//creates a new work queue record for an add hoc inspection
	public function createAdHocWorkQueue($assetID, $createdBy, $createdDate, $client)
	{
		try
		{
			//set db
			BaseActiveRecord::setClient($client);
			
			//create response format
			$responseData = [];
			try
			{
				$successFlag = 0;
				$workQueue = new WorkQueue;
				$workQueue->AssignedUserID = $createdBy;
				$workQueue->WorkQueueStatus = Constants::WORK_QUEUE_COMPLETED;
				$workQueue->CreatedBy = $createdBy;
				$workQueue->CreatedDate = $createdDate;
				$workQueue->tAssetID = $assetID;
				if($workQueue->save())
				{
					$successFlag = 1;
				}
				else
				{
					throw BaseActiveController::modelValidationException($workQueue);
				}
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $assetID);
			}
			//match response structure of inspection completeWork() 
			$responseData = [
				'WorkQueue' => (object)['WorkQueueID' => $workQueue->ID,
										'WorkQueueStatus' => $workQueue->WorkQueueStatus,
										'SuccessFlag' => $successFlag],
				'WorkOrder' => (object)['WorkOrderID' => null,
										'SuccessFlag' => 1,
										'Comments' => 'Work Order will be created on Task Out.']
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