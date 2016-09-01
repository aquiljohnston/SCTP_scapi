<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\WebManagementDispatch;
use app\modules\v1\modules\pge\models\WebManagementAssignedWorkQueue;
use app\modules\v1\modules\pge\models\WebManagementUsers;
use app\modules\v1\modules\pge\models\AssignedWorkQueue;
use app\modules\v1\modules\pge\models\UserLogin;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class DispatchController extends Controller 
{
	
	public $modelClass = 'app\modules\v1\modules\pge\models\PGEUser'; 

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
					'get-unassigned' => ['get'],
					'get-assigned' => ['get'],
					'get-surveyors' => ['get'],
					'dispatch' => ['post'],
					'unassign' => ['delete'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGetUnassigned($division = null, $workCenter = null, $mapPlat = null, $surveyType = null, $complianceMonth = null, $filter = null)
	{
		try
		{
			
			$assetQuery = WebManagementDispatch::find()->where(['Assigned' => 0]);
			
			if($division != null)
			{
				$assetQuery->andWhere(['Division'=>$division]);
			}
			
			if($workCenter != null)
			{
				$assetQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			
			if($mapPlat != null)
			{
				$assetQuery->andWhere(['MapPlat'=>$mapPlat]);
			}
			
			if($surveyType != null)
			{
				$assetQuery->andWhere(['SurveyType'=>$surveyType]);
			}
			
			if($complianceMonth != null)
			{
				$assetQuery->andWhere(['ComplianceYearMonth'=>$complianceMonth]);
			}
			
			if($filter != null)
			{
				$assetQuery->andFilterWhere([
				'or',
				['like', 'Division', $filter],
				['like', 'WorkCenter', $filter],
				['like', 'SurveyType', $filter],
				['like', 'MapPlat', $filter],
				['like', 'Notification ID', $filter],
				['like', 'ComplianceDueDate', $filter],
				['like', 'SAP Released', $filter],
				['like', 'Assigned', $filter],
				]);
			}
			
			$assets = $assetQuery->all();
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $assets;
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
	
	public function actionGetAssigned($division = null, $workCenter = null, $mapPlat = null, $status = null, $dispatchMethod = null, $complianceMonth = null, $filter = null)
	{
		try
		{
			$assetQuery = WebManagementAssignedWorkQueue::find();
			
			if($division != null)
			{
				$assetQuery->andWhere(['Division'=>$division]);
			}
			
			if($workCenter != null)
			{
				$assetQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			
			if($mapPlat != null)
			{
				$assetQuery->andWhere(['MapPlat'=>$mapPlat]);
			}
			
			if($status != null)
			{
				$assetQuery->andWhere(['Status'=>$status]);
			}
			
			if($complianceMonth != null)
			{
				$assetQuery->andWhere(['ComplianceYearMonth'=>$complianceMonth]);
			}
			
			if($filter != null)
			{
				$assetQuery->andFilterWhere([
				'or',
				['like', 'Division', $filter],
				['like', 'WorkCenter', $filter],
				['like', 'SurveyType', $filter],
				['like', 'MapPlat', $filter],
				['like', 'Notification ID', $filter],
				['like', 'Compliance Date', $filter],
				['like', 'Surveyor', $filter],
				['like', 'Employee Type', $filter],
				['like', 'Status', $filter],
				['like', 'Dispatch Method', $filter],
				['like', 'ComplianceYearMonth', $filter],
				]);
			}
			
			$assets = $assetQuery->all();
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $assets;
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
	
	public function actionGetAssignedWorkQueues()
	{
		try
		{
			WebManagementAssignedWorkQueue::setClient('CometTracker');
			$UID = BaseActiveController::getUserFromToken()->UserUID;
			
			$assignedWork = WebManagementAssignedWorkQueue::find()
				->where(['UserUID'=>$UID])
				->all();
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $assignedWork;
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
	
	public function actionGetSurveyors($filter = null)
	{
		// try
		// {
			//TODO need to add a new column to the view with lastname, firstname
			$userQuery = UserLogin::find()
				->select(['UserUID', new \yii\db\Expression("CONCAT(UserLastName, ', ', UserFirstName)as UserFullName"), 'UserLANID'])
				->orderBy('UserLastName');
			
			if($filter != null)
			{
				$userQuery->andFilterWhere([
				'or',
				['like', 'UserLastName', $filter],
				['like', 'UserFirstName', $filter],
				['like', 'UserLANID', $filter],
				]);
			}
			
			$users = $userQuery->asArray()->all();
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $users;
			return $response;
		// }
        // catch(ForbiddenHttpException $e)
        // {
            // throw new ForbiddenHttpException;
        // }
        // catch(\Exception $e)
        // {
            // throw new \yii\web\HttpException(400);
        // }
	}
	
	public function actionDispatch()
	{
		// try
		// {
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			$responseData = [];
			
			$assetCount = count($data['Assignments']);
			
			for($i = 0; $i < $assetCount; $i++)
			{
				BaseActiveRecord::setClient('CometTracker');
				$assignment = new AssignedWorkQueue;
				$assignment->SourceID = $data['SourceID'];
				$assignment->DispatchMethod = 'Dispatched';
				$assignment->AssignedDate = BaseActiveController::getDate();
				$assignment->CreatedUserUID = BaseActiveController::getUserFromToken()->UserUID;
				$assignment->ProjectID = 1;
				$assignment->ActiveFlag = 1;
				$assignment->Revision = 0;
				$assignment->ModifiedUserUID = BaseActiveController::getUserFromToken()->UserUID;
				$assignment->AssignedWorkQueueUID = BaseActiveController::generateUID('AssignedWorkQueue', $data['SourceID']);
				$assignment->AssignedInspectionRequestUID = $data['Assignments'][$i]['IR'];
				$assignment->AssignedUserUID = $data['Assignments'][$i]['User'];
				AssignedWorkQueue::setClient('pgedev');
				if($assignment->save())
				{
					$responseData[] = $assignment;
				}
			}
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		// }
        // catch(ForbiddenHttpException $e)
        // {
            // throw new ForbiddenHttpException;
        // }
        // catch(\Exception $e)
        // {
            // throw new \yii\web\HttpException(400);
        // }
	}
	
	public function actionUnassign()
	{
		try{
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			
			$count = count($data['Unassign']);
			
			for($i = 0; $i < $count; $i++)
			{
				AssignedWorkQueue::deleteAll(['AssignedWorkQueueUID' => $data['Unassign'][$i]]);
			}
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->statusCode = 204;
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