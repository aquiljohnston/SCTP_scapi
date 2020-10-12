<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\MileageEntry;
use app\modules\v3\models\MileageRate;
use app\modules\v3\models\MileageEntryEventHistory;
use app\modules\v3\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\query;

/**
 * MileageEntryController implements the CRUD actions for MileageEntry model.
 */
class MileageEntryController extends BaseActiveController
{
    public $modelClass = 'app\modules\v3\models\MileageEntry'; 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'create-task' => ['post'],
					'deactivate' => ['put'],
					'view-entries' => ['get'],
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
	
	/**
     * Create New Mileage Entry and Activity in CT DB
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreateTask()
    {
        try {
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('createTaskEntry');

			$successFlag = 0;
			$warningMessage = '';
			
            //get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
			
			$mileageRate = (float)$data['MileageRate'];
			
			// set up db connection
			$connection = BaseActiveRecord::getDb();
			$processJSONCommand = $connection->createCommand("EXECUTE spAddMileage :MileageCardID , :Date, :TotalMiles, :MileageType, :CreatedByUserName, :MileageRate");
			$processJSONCommand->bindParam(':MileageCardID', $data['MileageCardID'], \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':Date', $data['Date'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':TotalMiles', $data['TotalMiles']);
			$processJSONCommand->bindParam(':MileageType', $data['MileageType'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':CreatedByUserName', $data['CreatedByUserName'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':MileageRate', $mileageRate, \PDO::PARAM_STR);
			$processJSONCommand->execute();
			$successFlag = 1;			
        } catch (ForbiddenHttpException $e) {
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], [
                'MileageCardID' => $data['MileageCardID'],
                'Date' => $data['Date'],
                'CreatedByUserName' => $data['CreatedByUserName'],
                'SuccessFlag' => $successFlag
            ]);
			$warningMessage = 'An error occurred.';
        }
		
		//build response format
		$dataArray =  [
			'MileageCardID' => $data['MileageCardID'],
			'SuccessFlag' => $successFlag,
			'WarningMessage' => $warningMessage,
		];
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->data = $dataArray;
		
		return $response;
    }
	
	public function actionDeactivate($entryID)
	{
		try{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryDeactivate');

			//get date and current user
			$modifiedBy = BaseActiveController::getUserFromToken()->UserName;
			$modifiedDate = Parent::getDate();
			
			$mileageEntry = MileageEntry::findOne($entryID);
			$successFlag = 0;
			
			try{
				//pass current data to new history record
				if(self::createHistoryRecord($mileageEntry, $modifiedBy, $modifiedDate, 'Deactivated')){
					//delete the record, to avoid constraint issues
					if($mileageEntry->delete()){
						$successFlag = 1;
					}
				}
			}catch(Exception $e){
				$transaction->rollBack();
			}
			
			$transaction->commit();
			
			$responseData = [
				'EntryID' => $mileageEntry->MileageEntryID,
				'SuccessFlag' => $successFlag
			];
			$response->data = $responseData;
			return $response;
		} catch (ForbiddenHttpException $e) {
            BaseActiveController::logError($e, 'Forbidden http exception');
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson('MileageEntryID: ' . $entryID, $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionViewEntries($cardID, $date){
		try{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
				
			//RBAC permission check
			PermissionsController::requirePermission('mileageEntryView');
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$entriesQuery = new Query;
			$entriesQuery->select('*')
				->from(["fnMileageCardEntryDetailsByMileageCardAndDate(:cardID, :date)"])
				->addParams([':cardID' => $cardID, ':date' => $date]);
			$entries = $entriesQuery->all(BaseActiveRecord::getDb());
			
			$mileageRate = MileageRate::find()
				->select(["concat(MileageType, '(' , Rate , ')') as MileageType", 'Rate'])
				->all();
				
			$rates = [];
			$rates[''] = 'Select';
			//loop data to format response
			foreach($mileageRate as $rate){
				$rates[(string)$rate->Rate] = $rate->MileageType;
			}
			
			$transaction->commit();

			$dataArray['entries'] = $entries;
			$dataArray['rates'] = $rates;
				
			$response->data = $dataArray; 
			return $response;
		}catch (ForbiddenHttpException $e){
            BaseActiveController::logError($e, 'Forbidden http exception');
			throw new ForbiddenHttpException;
		}catch(\Exception $e){
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionUpdate(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			//get body object
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response object
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryUpdate');

			//get date and current user
			$modifiedBy = self::getUserFromToken()->UserName;
			$modifiedDate = Parent::getDate();
			
			//get current record
			$mileageEntry = MileageEntry::findOne($data['MileageEntryID']);
			$successFlag = 0;
			
			//unset milegerate if value is blank to not override data
			if($data['MileageRate'] == null)
				unset($data['MileageRate']);
			
			try{
				//pass current data to new history record
				if(self::createHistoryRecord($mileageEntry, $modifiedBy, $modifiedDate, 'Updated')){
					//updated record with new data
					$mileageEntry->attributes = $data;  
					$mileageEntry->MileageEntryModifiedBy = $modifiedBy;
					$mileageEntry->MileageEntryModifiedDate = $modifiedDate;
					if($mileageEntry-> update()){
						$successFlag = 1;
					}
				}
			}catch(Exception $e){
				$transaction->rollBack();
			}
			
			$transaction->commit();
			
			$responseData = [
				'EntryID' => $mileageEntry->MileageEntryID,
				'SuccessFlag' => $successFlag
			];
			$response->data = $responseData;
			return $response;
		} catch (ForbiddenHttpException $e) {
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
			throw new \yii\web\HttpException(400);
		}
	}
	
	//helper function 
	//params mileageEntry model, username of modifying user, and type of change being performed
	//returns true if successful
	private function createHistoryRecord($mileageEntry, $modifiedBy, $modifiedDate, $changeType){
		//new history record
		$historyModel = new MileageEntryEventHistory;
		$historyModel->Attributes = $mileageEntry->attributes;
		$historyModel->ChangeMadeBy = $modifiedBy;
		$historyModel->ChangeDateTime = $modifiedDate;
		$historyModel->Change = $changeType;
		if($historyModel->save()){
			return true;
		}
		return false;
	}
}
