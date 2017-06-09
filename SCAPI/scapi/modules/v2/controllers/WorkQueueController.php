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
		//TODO add additional logging for incoming json and validation errors
		try
		{
			//set db
			BaseActiveRecord::setClient($client);
			
			//create response format
			$responseData = [];
			
			//count number of items to unassign
			$acceptedCount = count($data);
			
			//code is sent in json, do we want to send text and lookup code isntead?
			//get assinged status code
			//$assignedCode = self::statusCodeLookup('Assigned');
			
			//process accepted
			//nested for loop needed because map grid does not exist in work queue
			//planned to iterate on this design and change to work order id
			for($i = 0; $i < $acceptedCount; $i++)
			{
				$successFlag = 0;
				$workQueue = WorkQueue::find()
					->where(['ID' => $data[$i]['WorkQueueID']])
					->andWhere(['not in', 'WorkQueueStatus', [101, 102]])
					->one();
				if($workQueue != null)
				{
					$workQueue->WorkQueueStatus = $data[$i]['WorkQueueStatus'];
					$workQueue->ModifiedBy = $modifiedBy;
					$workQueue->ModifiedDate = $data[$i]['ModifiedDate'];
					//if work queue is already accepted and no change exist update will fail and return successFlag of 0
					if($workQueue->update())
					{
						$successFlag = 1;
					}
				}
				$responseData[] = [
					'WorkQueueID' => $data[$i]['WorkQueueID'],
					'AssignedUserID' => $data[$i]['AssignedUserID'],
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
}