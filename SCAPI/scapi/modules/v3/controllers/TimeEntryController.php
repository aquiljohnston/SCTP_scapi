<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\models\TimeEntry;
use app\modules\v3\models\TimeEntryEventHistory;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TimeEntryController implements the CRUD actions for TimeEntry model.
 */
class TimeEntryController extends BaseActiveController
{
    public $modelClass = 'app\modules\v3\models\TimeEntry'; 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'deactivate' => ['put'],
					'deactivate-by-task' => ['put'],
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
	
	use ViewMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	public function actionDeactivate($entryID){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			// RBAC permission check
			PermissionsController::requirePermission('timeEntryDeactivate');

			//get date and current user
			$modifiedBy = BaseActiveController::getUserFromToken()->UserName;
			$modifiedDate = Parent::getDate();
			
			$timeEntry = TimeEntry::findOne($entryID);
			$successFlag = 0;
			
			try{
				//pass current data to new history record
				if(self::createHistoryRecord($timeEntry, $modifiedBy, $modifiedDate, 'Deactivated')){
					//delete the record, to avoid constraint issues
					if($timeEntry->delete()){
						$successFlag = 1;
					}
				}
			}catch(Exception $e){
				$transaction->rollBack();
			}
			
			$transaction->commit();
			
			$responseData = [
				'EntryID' => $timeEntry->TimeEntryID,
				'SuccessFlag' => $successFlag
			];
			$response->data = $responseData;
			return $response;
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionDeactivateByTask()
	{		
		try{
			//set db target
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
				//avoid pass by reference error in prod
				$taskName = json_encode($entry['taskName']);
				$taskDay = array_key_exists('day', $entry) ? $entry['day'] : null;
				//call SPROC to deactivateTimeEntry
				try {
					$connection = BaseActiveRecord::getDb();
					$transaction = $connection->beginTransaction(); 
					$timeCardCommand = $connection->createCommand("EXECUTE spDeactivateTimeEntry :PARAMETER1,:PARAMETER2,:PARAMETER3,:PARAMETER4");
					$timeCardCommand->bindParam(':PARAMETER1', $entry['timeCardID'], \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':PARAMETER2', $taskName, \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':PARAMETER3', $taskDay, \PDO::PARAM_INT);
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
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
		}
	}
	
	//helper function 
	//params timeEntry model, username of modifying user, and type of change being performed
	//returns true if successful
	private function createHistoryRecord($timeEntry, $modifiedBy, $modifiedDate, $changeType){
		//new history record
		$historyModel = new TimeEntryEventHistory;
		$historyModel->Attributes = $timeEntry->attributes;
		$historyModel->ChangeMadeBy = $modifiedBy;
		$historyModel->ChangeDateTime = $modifiedDate;
		$historyModel->Change = $changeType;
		if($historyModel->save()){
			return true;
		}
		return false;
	}
}