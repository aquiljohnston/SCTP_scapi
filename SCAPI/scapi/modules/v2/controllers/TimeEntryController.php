<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\models\TimeEntry;
use app\modules\v2\models\SCUser;
use app\modules\v2\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TimeEntryController implements the CRUD actions for TimeEntry model.
 */
class TimeEntryController extends BaseActiveController
{
    public $modelClass = 'app\modules\v2\models\TimeEntry'; 

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
			$headers = getallheaders();
			TimeEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeEntryView');
			
			$timeEntry = TimeEntry::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $timeEntry;
			
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionDeactivate()
	{		
		try{
			//set db target
			$headers = getallheaders();
			TimeEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeEntryDeactivate');
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//parse json
			$deactivatedBy = self::getUserFromToken()->UserName;
			$entryIDs = $data["entryArray"];
			
			//get mileage entries
			foreach($entryIDs as $id)
			{
				$approvedEntries[]= TimeEntry::findOne($id);
			}
			
			try
			{
				//create transaction
				$connection = \Yii::$app->db;
				$transaction = $connection->beginTransaction(); 
			
				foreach($approvedEntries as $entry)
				{
					$entry-> TimeEntryActiveFlag = 0;
					$entry-> TimeEntryModifiedDate = Parent::getDate();
					$entry-> TimeEntryModifiedBy = $deactivatedBy;
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