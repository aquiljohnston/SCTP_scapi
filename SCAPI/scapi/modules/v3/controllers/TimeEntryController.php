<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\models\TimeEntry;
use app\modules\v3\models\TimeEntryEventHistory;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\controllers\PtoController;
use app\modules\v3\constants\Constants;
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
					'deactivate-by-day' => ['put'],
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
	
	public function actionDeactivate(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true)['data'];
			
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
			
			$timeEntry = TimeEntry::findOne($data['entryID']);
			$successFlag = 0;
			
			try{
				//check if record is PTO then pass to function as array to update pto record
				if($timeEntry->TimeEntryChartOfAccount == Constants::PTO_PAYROLL_HOURS_ID){
					$timeEntryArray = [];
					$timeEntryArray[] = $timeEntry;
					PtoController::updatePTO($timeEntryArray);
				}
				
				//pass current data to new history record
				if(self::createHistoryRecord($timeEntry, $modifiedBy, $modifiedDate, 'Deactivated', $data['timeReason'])){
					//delete the record, to avoid constraint issues
					if($timeEntry->delete()){
						$successFlag = 1;
					}
				}
			}catch(Exception $e){
				$transaction->rollBack();
				BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
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
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionDeactivateByDay(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
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
				$timeCardID = $entry['timeCardID'];
				$taskDay = $entry['day'];
				//call SPROC to deactivateTimeEntry
				try {
					$connection = BaseActiveRecord::getDb();
					$transaction = $connection->beginTransaction();
					//if PTO get affected time entries
					// if($taskName == '["Task OTHER [PTO]"]'){
						// $timeEntryArray = [];
						// $timeEntryQuery = TimeEntry::find()
							// ->where([ 'and',
								// ['TimeEntryTimeCardID' => $timeCardID],
								// ['TimeEntryChartOfAccount' => Constants::PTO_PAYROLL_HOURS_ID]
							// ]);
						// if($taskDay != null)
							// $timeEntryQuery->andWhere(['CAST(TimeEntryStartTime AS date)' => $taskDay]);
						// $timeEntryArray = $timeEntryQuery->all();
						// //pass time entries to function for updating pto records
						// PtoController::updatePTO($timeEntryArray);
					// }
					$timeCardCommand = $connection->createCommand("EXECUTE spDeactivateTimeEntry_new :TimeCardID,:TimeEntryDate,:UserName,:TimeReason");
					$timeCardCommand->bindParam(':TimeCardID', $timeCardID, \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':TimeEntryDate', $taskDay, \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':UserName', $username, \PDO::PARAM_STR);
					$timeCardCommand->bindParam(':TimeReason', $entry['timeReason'], \PDO::PARAM_STR);
					$timeCardCommand->execute();
					$transaction->commit();
					$success = 1;
				} catch (Exception $e) {
					$transaction->rollBack();
					BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
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
	
	
	//todo pending removal replaced by actionDeactivateByDay
	public function actionDeactivateByTask(){
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
				$timeCardID = $entry['timeCardID'];
				$taskName = json_encode($entry['taskName']);
				$taskDay = array_key_exists('day', $entry) ? $entry['day'] : null;
				//call SPROC to deactivateTimeEntry
				try {
					$connection = BaseActiveRecord::getDb();
					$transaction = $connection->beginTransaction();
					//if PTO get affected time entries
					if($taskName == '["Task OTHER [PTO]"]'){
						$timeEntryArray = [];
						$timeEntryQuery = TimeEntry::find()
							->where([ 'and',
								['TimeEntryTimeCardID' => $timeCardID],
								['TimeEntryChartOfAccount' => Constants::PTO_PAYROLL_HOURS_ID]
							]);
						if($taskDay != null)
							$timeEntryQuery->andWhere(['CAST(TimeEntryStartTime AS date)' => $taskDay]);
						$timeEntryArray = $timeEntryQuery->all();
						//pass time entries to function for updating pto records
						PtoController::updatePTO($timeEntryArray);
					}
					$timeCardCommand = $connection->createCommand("EXECUTE spDeactivateTimeEntry :TimeCardID,:TimeEntryTitleJSON,:TimeEntryDate,:UserName,:TimeReason");
					$timeCardCommand->bindParam(':TimeCardID', $timeCardID, \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':TimeEntryTitleJSON', $taskName, \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':TimeEntryDate', $taskDay, \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':UserName', $username, \PDO::PARAM_STR);
					$timeCardCommand->bindParam(':TimeReason', $entry['timeReason'], \PDO::PARAM_STR);
					$timeCardCommand->execute();
					$transaction->commit();
					$success = 1;
				} catch (Exception $e) {
					$transaction->rollBack();
					BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
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
	private function createHistoryRecord($timeEntry, $modifiedBy, $modifiedDate, $changeType, $timeReason){
		//new history record
		$historyModel = new TimeEntryEventHistory;
		$historyModel->Attributes = $timeEntry->attributes;
		$historyModel->ChangeMadeBy = $modifiedBy;
		$historyModel->ChangeDateTime = $modifiedDate;
		$historyModel->Change = $changeType;
		$historyModel->TimeEntryComment = $timeReason;
		if($historyModel->save()){
			return true;
		}
		return false;
	}
}