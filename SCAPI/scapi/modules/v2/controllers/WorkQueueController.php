<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\authentication\TokenAuth;
use app\modules\v2\models\BaseActiveRecord;
//use app\modules\v2\models\AvailableWorkQueue;
use app\modules\v2\models\AssignedWorkQueue;
//use app\modules\v2\models\WorkOrder;
use app\modules\v2\models\WorkQueue;
//use app\modules\v2\models\StatusLookup;
use app\modules\v2\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Connection;

class WorkQueueController extends Controller 
{
	public static $inProgress = 101;
	public static $completed = 102;
	
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
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			$responseArray = [];
			$workQueue = AssignedWorkQueue::find()
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
				,AssignedToID')
				->where(['AssignedToID' => $userID])
				->all();
			
			$responseArray['WorkQueue'] = $workQueue;
					
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
	
	//fuction called by activity to parse and accept work queues
	public static function accept($data, $client, $modifiedBy)
	{
		try
		{
			//set db
			BaseActiveRecord::setClient($client);
			
			//create response format
			$responseData = [];
			
			//count number of items to accept
			$acceptedCount = count($data);
			
			//process accepted
			for($i = 0; $i < $acceptedCount; $i++)
			{
				//try catch to log individual errors
				try
				{
					$successFlag = 0;
					$workQueue = WorkQueue::find()
						->where(['ID' => $data[$i]['WorkQueueID']])
						->andWhere(['not in', 'WorkQueueStatus', [self::$inProgress, self::$completed]])
						->one();
					if($workQueue != null)
					{
						$workQueue->WorkQueueStatus = $data[$i]['WorkQueueStatus'];
						$workQueue->ModifiedBy = $modifiedBy;
						$workQueue->ModifiedDate = $data[$i]['ModifiedDate'];
						if($workQueue->update())
						{
							$successFlag = 1;
						}
						else
						{
							throw BaseActiveController::modelValidationException($workQueue);
						}
					}
					else{
						$successFlag = 1;
					}
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i]);
				}
				$responseData[] = [
					'WorkQueueID' => $data[$i]['WorkQueueID'],
					'SuccessFlag' => $successFlag
				];
			}
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
	
	//fuction called by activity to parse and accept work queues
	public static function complete($workQueueID, $workQueueStatus, $client, $modifiedBy, $modifiedDate)
	{
		try
		{
			//set db
			BaseActiveRecord::setClient($client);
			
			//create response format
			$responseData = [];
			
			//try catch to log individual errors
			try
			{
				$successFlag = 0;
				$workQueue = WorkQueue::find()
					->where(['ID' => $workQueueID])
					->one();
				if($workQueue != null)
				{
					if($workQueue->WorkQueueStatus != self::$completed)
					{
						$workQueue->WorkQueueStatus = $workQueueStatus;
						$workQueue->ModifiedBy = $modifiedBy;
						$workQueue->ModifiedDate = $modifiedDate;
						if($workQueue->update())
						{
							$successFlag = 1;
						}
						else
						{
							throw BaseActiveController::modelValidationException($workQueue);
						}
					}
					else{
						$successFlag = 1;
					}
				}
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $workQueueID);
			}
			$responseData = [
				'WorkQueueID' => $workQueueID,
				'WorkQueueStatus' => $workQueueStatus,
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
				$workQueue->WorkQueueStatus = self::$completed;
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
			$responseData = [
				'WorkQueueID' => $workQueue->ID,
				'WorkQueueStatus' => $workQueue->WorkQueueStatus,
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