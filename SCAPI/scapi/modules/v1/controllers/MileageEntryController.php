<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\MileageEntry;
use app\modules\v1\models\SCUser;
use app\modules\v1\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * MileageEntryController implements the CRUD actions for MileageEntry model.
 */
class MileageEntryController extends BaseActiveController
{
    public $modelClass = 'app\modules\v1\models\MileageEntry'; 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'deactivate' => ['put'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	public function actionView($id)
	{
		try
		{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryView');
			
			//$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
			$mileageEntry = MileageEntry::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $mileageEntry;
			
			return $response;
		}
		catch(\Exception $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionCreate()
	{
		try
		{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryCreate');
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new MileageEntry(); 
			$model->attributes = $data;

			
			$userID = self::getUserFromToken()->UserID;
			$model->MileageEntryCreatedBy = $userID;


			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			if($model-> save())
			{
				$response->setStatusCode(201);
				$response->data = $model; 
			}
			else
			{
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
			return $response;
		}
		catch(\Exception $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionDeactivate()
	{
		try
		{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryDeactivate');
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//parse json
			$deactivatedBy = $data["deactivatedBy"];
			$entryIDs = $data["entryArray"];
			
			//get mileage entries
			foreach($entryIDs as $id)
			{
				$approvedEntries[]= MileageEntry::findOne($id);
			}
			
			//try to approve time cards
			try
			{
				//create transaction
				$connection = \Yii::$app->db;
				$transaction = $connection->beginTransaction(); 
			
				foreach($approvedEntries as $entry)
				{
					$entry-> MileageEntryActiveFlag = 0;
					$entry-> MileageEntryModifiedDate = Parent::getDate();
					$entry-> MileageEntryModifiedBy = $deactivatedBy;
					$entry-> update();
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $approvedEntries; 
				return $response;
			}
			//if transaction fails rollback changes and send error
			catch(Exception $e)
			{
				$transaction->rollBack();
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				return $response;
			}
			
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
