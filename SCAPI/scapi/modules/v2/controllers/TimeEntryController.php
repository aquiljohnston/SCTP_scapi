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
use app\modules\v2\models\BaseActiveRecord;

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
					'view' => ['get'],
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
			$entries = json_decode($put, true)['entries'];

			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			//get current user to set deactivated by
			$username = BaseActiveController::getUserFromToken()->UserName;

			foreach ($entries as $entry) {
				//SPROC has no return so just in case we need a flag.
				$success = 0;
				//call SPROC to deactivateTimeEntry
				try {
				$connection = BaseActiveRecord::getDb();
				$transaction = $connection->beginTransaction(); 
				$timeCardCommand = $connection->createCommand("EXECUTE spDeactivateTimeEntry :PARAMETER1,:PARAMETER2,:PARAMETER3,:PARAMETER4");
				$timeCardCommand->bindParam(':PARAMETER1', $entry['timeCardID'], \PDO::PARAM_INT);
				$timeCardCommand->bindParam(':PARAMETER2', json_encode($entry['taskName']), \PDO::PARAM_INT);
				$timeCardCommand->bindParam(':PARAMETER3', array_key_exists('day', $entry) ? $entry['day'] : null, \PDO::PARAM_INT);
				$timeCardCommand->bindParam(':PARAMETER4', $username, \PDO::PARAM_STR);
				$timeCardCommand->execute();
				$transaction->commit();
				$success = 1;
				} catch (Exception $e) {
					$transaction->rollBack();
				}
			}
			$response->data = $success; 
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}