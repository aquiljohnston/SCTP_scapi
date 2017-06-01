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
//use app\modules\v2\models\AssignedWorkQueue;
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
					'accept' => ['put'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionAccept()
	{
		//TODO add additional logging for incoming json and validation errors
		try
		{
			//get headers
			$headers = getallheaders();
			//get modified by
			$modifiedBy = BaseActiveController::getClientUser($headers['X-Client'])->UserID;
			//set db
			BaseActiveRecord::setClient($headers['X-Client']);
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true);
			//create response format
			$responseData = [];
			
			//count number of items to unassign
			$acceptedCount = count($data['workQueue']);
			
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
					->where(['WorkOrderID' => $data['workQueue'][$i]['WorkOrderID']])
					->andWhere(['AssignedUserID' => $data['workQueue'][$i]['AssignedUserID']])
					->andWhere(['not in', 'WorkQueueStatus', [101, 102]])
					->one();
				if($workQueue != null)
				{
					$workQueue->WorkQueueStatus = $data['workQueue'][$i]['WorkQueueStatus'];
					$workQueue->ModifiedBy = $modifiedBy;
					$workQueue->ModifiedDate = BaseActiveController::getDate();
					if($workQueue->update())
					{
						$successFlag = 1;
					}
				}
				$responseData[] = [
					'WorkOrderID' => $data['workQueue'][$i]['WorkOrderID'],
					'AssignedUserID' => $data['workQueue'][$i]['AssignedUserID'],
					'SuccessFlag' => $successFlag
				];
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
            throw new \yii\web\HttpException(400);
        }
	}
}