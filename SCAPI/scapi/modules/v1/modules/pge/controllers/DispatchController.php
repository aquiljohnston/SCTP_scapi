<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
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
use yii\db\Connection;
use yii\helpers\VarDumper;

class DispatchController extends Controller 
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
					'get-unassigned' => ['get'],
					'get-assigned' => ['get'],
					'get-surveyors' => ['get'],
					'dispatch' => ['post'],
					'unassign' => ['put'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGetUnassigned($division = null, $workCenter = null, $surveyType = null, $floc = null, $complianceMonth = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//set db
			$headers = getallheaders();
			WebManagementDispatch::setClient($headers['X-Client']);
			
			$assetQuery = WebManagementDispatch::find()->where(['Assigned' => 0]);
			
			if($division != null)
			{
				$assetQuery->andWhere(['Division'=>$division]);
			}
			
			if($workCenter != null)
			{
				$assetQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			
			if(!($surveyType == null || $surveyType == 'All'))
			{
				$assetQuery->andWhere(['SurveyType'=>$surveyType]);
			}
			
			if(!($floc == null || $floc == 'All'))
			{
				$assetQuery->andWhere(['FLOC'=>$floc]);
			}
			
			if(!($complianceMonth == null || $complianceMonth == 'All'))
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
				['like', 'FLOC', $filter],
				['like', 'Notification ID', $filter],
				['like', 'ComplianceDueDate', $filter],
				['like', 'SAP Released', $filter],
				['like', 'Assigned', $filter],
				]);
			}

			// set pagination
            $countAssetQuery = clone $assetQuery;
            $pages = new Pagination(['totalCount' => $countAssetQuery->count()]);
            $offset = $listPerPage*($page-1);
            $pageSize = ceil($countAssetQuery->count()/$listPerPage);
            $pages->setPageSize($pageSize);

            $assets = $assetQuery->offset($offset)
                ->limit($listPerPage)
                ->all();


            $responseArray = [];

            $responseArray["pages"] = $pages;
            $responseArray["assets"] = $assets;
			
			//send response
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
	
	public function actionGetAssigned($division = null, $workCenter = null, $surveyType = null, $floc = null, $status = null, $dispatchMethod = null, $complianceMonth = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			$headers = getallheaders();
			WebManagementAssignedWorkQueue::setClient($headers['X-Client']);
			
			$assetQuery = WebManagementAssignedWorkQueue::find();
			
			if($division != null)
			{
				$assetQuery->andWhere(['Division'=>$division]);
			}
			
			if($workCenter != null)
			{
				$assetQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			
			if(!($surveyType == null || $surveyType == 'All'))
			{
				$assetQuery->andWhere(['SurveyType'=>$surveyType]);
			}
			
			if(!($floc == null || $floc == 'All'))
			{
				$assetQuery->andWhere(['FLOC'=>$floc]);
			}
			
			if($status != null)
			{
				$assetQuery->andWhere(['Status'=>$status]);
			}
			
			if($dispatchMethod != null)
			{
				$assetQuery->andWhere(['DispatchMethod'=>$dispatchMethod]);
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
				['like', 'NotificationID', $filter],
				['like', 'ComplianceDate', $filter],
				['like', 'Surveyor', $filter],
				['like', 'EmployeeType', $filter],
				['like', 'Status', $filter],
				['like', 'DispatchMethod', $filter],
				['like', 'ComplianceYearMonth', $filter],
				]);
			}

            // set pagination
            $countAssetQuery = clone $assetQuery;
            $pages = new Pagination(['totalCount' => $countAssetQuery->count()]);
            $offset = $listPerPage*($page-1);
            $pageSize = ceil($countAssetQuery->count()/$listPerPage);
            $pages->setPageSize($pageSize);

            $assets = $assetQuery->offset($offset)
                ->limit($listPerPage)
                ->all();

            $responseArray = [];

            $responseArray["pages"] = $pages;
            $responseArray["assets"] = $assets;

			//send response
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
	
	public function actionGetSurveyors($workCenter = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			$headers = getallheaders();
			UserLogin::setClient($headers['X-Client']);
			
			//TODO need to add a new column to the view with lastname, firstname
			$userQuery = UserLogin::find()
				->select('UserUID, UserFullName, UserLANID, WorkCenter');
				
			
			if($workCenter != null)
			{
				$userQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			
			if($filter != null)
			{
				$userQuery->andFilterWhere([
				'or',
				['like', 'UserLastName', $filter],
				['like', 'UserFirstName', $filter],
				['like', 'UserLANID', $filter],
				['like', 'WorkCenter', $filter],
				]);
			}

            //set pagination
            $countUserQuery = clone $userQuery;
            $pages = new Pagination(['totalCount' => $countUserQuery->count()]);
            $offset = $listPerPage*($page-1);
            $pageSize = ceil($countUserQuery->count()/$listPerPage);
            $pages->setPageSize($pageSize);

            $users = $userQuery->offset($offset)
                ->asArray()
                ->limit($listPerPage)
				->orderBy('UserFullName')
                ->all();

            $responseArray = [];
            $responseArray["pages"] = $pages;
            $responseArray["users"] = $users;
			
			//send response
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
	
	public function actionDispatch()
	{
		try
		{
			$headers = getallheaders();
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			$responseData = [];
			
			$assetCount = count($data['Assignments']);
			
			for($i = 0; $i < $assetCount; $i++)
			{
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
				$userUID = BaseActiveController::getUserFromToken()->UserUID;
				
				AssignedWorkQueue::setClient($headers['X-Client']);
				$assignment = new AssignedWorkQueue;
				$assignment->SourceID = $data['SourceID'];
				$assignment->DispatchMethod = 'Dispatched';
				$assignment->AssignedDate = BaseActiveController::getDate();
				$assignment->CreatedUserUID = $userUID;
				$assignment->ProjectID = 1;
				$assignment->ActiveFlag = 1;
				$assignment->Revision = 0;
				$assignment->ModifiedUserUID = $userUID;
				$assignment->AssignedWorkQueueUID = BaseActiveController::generateUID('AssignedWorkQueue', $data['SourceID']);
				$assignment->AssignedInspectionRequestUID = $data['Assignments'][$i]['IR'];
				$assignment->AssignedUserUID = $data['Assignments'][$i]['User'];
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
	
	public function actionUnassign()
	{
		try{
			//get UID of user making request
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$UserUID = BaseActiveController::getUserFromToken()->UserUID;
			
			$headers = getallheaders();
			AssignedWorkQueue::setClient($headers['X-Client']);
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			$count = count($data['Unassign']);
			$responseData = [];
			
			for($i = 0; $i < $count; $i++)
			{
				//Find Existing Record
				$previousRecord = AssignedWorkQueue::find()
					->where(['AssignedWorkQueueUID' => $data['Unassign'][$i]])
					->andWhere(['ActiveFlag' => 1])
					->one();
				//Deactivate Previous Record
				$previousRecord->ActiveFlag = 0;
				//get previous record revision and increment by one
				$revisionCount =$previousRecord->Revision +1;
				if($previousRecord->update())
				{
					//Create new inactive record for audit purposes
					$newRecord = new AssignedWorkQueue();
					$newRecord->attributes = $previousRecord->attributes;
					$newRecord->Revision = $revisionCount;
					$newRecord->RevisionComments = 'Unassigned';
					$newRecord->ModifiedUserUID = $UserUID;
					
					if($newRecord->save())
					{
						$responseData[] = ['AssignedWorkQueueUID'=>$data['Unassign'][$i], 'Success'=>1];
					}
					else
					{
						$responseData[] = ['AssignedWorkQueueUID'=>$data['Unassign'][$i], 'Success'=>0];
					}
				}
				else
				{
					$responseData[] = ['AssignedWorkQueueUID'=>$data['Unassign'][$i], 'Success'=>0];
				}
				
			}
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->statusCode = 200;
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