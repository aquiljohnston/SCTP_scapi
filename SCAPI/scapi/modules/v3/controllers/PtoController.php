<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\db\Query;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\constants\Constants;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\controllers\TaskController;
use app\modules\v3\models\PTO;
use app\modules\v3\models\PTOHistory;
use app\modules\v3\models\PTOMediator;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\SCUser;
use DateTime;

class PtoController extends Controller{

	public function behaviors(){
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = [
                'class' => VerbFilter::className(),
                'actions' => [
					'create' => ['post'],
					'get-balance' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionCreate(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			$createdBy = BaseActiveController::getUserFromToken()->UserName;

			// RBAC permission check
			PermissionsController::requirePermission('ptoCreate');

			//capture post body
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			//archive json
			BaseActiveController::archiveJson(json_encode($data), 'PTOCreate', $createdBy, BaseActiveController::urlPrefix());
			
			if(array_key_exists('PTO', $data)){
				//pull data from envelope
				$data = $data['PTO'];
				//start transaction
				$db = BaseActiveRecord::getDb();
				$transaction = $db->beginTransaction();

				//create response array
				$responseData = [];

				//try catch to log expense object error
				try{					
					$successFlag = 0;
					$timeEntryResults = [];
					$pto = new PTO;
					$pto->attributes = $data;

					if ($pto->save()){
						//save PTOMediatorRecord
						self::savePTOMediator($pto);
						//save time entries
						foreach($data['TimeEntry'] as $entry){
							$results = TaskController::addActivityAndTime($entry);
							$timeEntryResults[] = [
								'TaskName' => $entry['TaskName'],
								'Date' => $entry['Date'],
								'successFlag' => $results['successFlag']
							];
						}
						$successFlag  = 1;
					} else {
						throw BaseActiveController::modelValidationException($pto);
					}
					//commit transaction
					$transaction->commit();
				}catch(yii\db\Exception $e){
					//if db exception is 2601/2627, duplicate contraint then success
					if(in_array($e->errorInfo[1], array(2601, 2627))){
						//if duplicate constraint
						$successFlag  = 1;
					}else{
						$transaction->rollback();
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
						$successFlag = 0;
					}
				}
				$responseData['PTO'][] = [
					'PTOUID' => $data['PTOUID'],
					'RefProjectID' => $data['RefProjectID'],
					'SCCEmployeeID' => $data['SCCEmployeeID'],
					'SuccessFlag' => $successFlag,
					'TimeEntry' => $timeEntryResults
				];
			}else{
				$responseData = (object)[];
			}
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;

			//return response data
			$response->data = $responseData;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(UnauthorizedHttpException $e) {
            throw new UnauthorizedHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
	}
	
	//get user PTO balance from time card ID
	public function actionGetBalance($timeCardID){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			// RBAC permission check
			PermissionsController::requirePermission('ptoGetBalance');
			
			//start transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			$ptoData = self::queryBalance($timeCardID, $db);
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			$responseArray['ptoData'] = $ptoData;

			//return response data
			$response->data = $responseArray;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(UnauthorizedHttpException $e) {
            throw new UnauthorizedHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
	}
	
	//save PTOMediator Record
	public static function savePTOMediator($pto){
		//get userID from ref ID
		$userID = SCUser::find()
			->select('UserID')
			->innerJoin('TimeCardTb', 'TimeCardTb.TimeCardTechID = UserTb.UserID')
			->where(['TimeCardID' => $pto->TimeCardID])
			->one();
		
		$ptoMediator = new PTOMediator;
		$ptoMediator->PendingBalance = $pto->NewBalance;
		$ptoMediator->DeltaChange = $pto->PreviousBalance - $pto->NewBalance;
		$ptoMediator->DeltaTimeStamp = BaseActiveController::getDate();
		$ptoMediator->PTOID = $pto->ID;
		$ptoMediator->UserID = $userID->UserID;
		
		if(!$ptoMediator->save())
			throw BaseActiveController::modelValidationException($ptoMediator);
	}
	
	//execute query to fetch PTOBalance Data
	public static function queryBalance($timeCardID, $db){
		//check if pto mediator record exist, if true grab most recent value else grab balance from userTB
		$mediatorQuery = new Query;
		$mediatorQuery->select('ID')
			->from('PTOMediator')
			->innerJoin('TimeCardTb', 'TimeCardTb.TimeCardTechID = PTOMediator.UserID')
			->where(['TimeCardTb.TimeCardID' => $timeCardID]);
		$mediatorCount = $mediatorQuery->count('*', $db);
		if($mediatorCount){
			$ptoQuery = new Query;
			$ptoQuery->select('PTOMediator.PendingBalance AS PTOBalance, UserTb.SCCEmployeeID, ProjectTb.ProjectReferenceID')
				->from('PTOMediator')
				->innerJoin('UserTb', 'UserTb.UserID = PTOMediator.UserID')
				->innerJoin('TimeCardTb', 'TimeCardTb.TimeCardTechID = PTOMediator.UserID')
				->innerJoin('ProjectTb', 'ProjectTb.ProjectID = TimeCardTb.TimeCardProjectID')
				->where(['TimeCardTb.TimeCardID' => $timeCardID])
				->orderBy('PTOMediator.DeltaTimeStamp DESC');
			$ptoData = $ptoQuery->one($db);
		}else{			
			$ptoQuery = new Query;
			$ptoQuery->select('UserTb.PTOBalance, UserTb.SCCEmployeeID, ProjectTb.ProjectReferenceID')
				->from('UserTb')
				->innerJoin('TimeCardTb', 'TimeCardTb.TimeCardTechID = UserTb.UserID')
				->innerJoin('ProjectTb', 'ProjectTb.ProjectID = TimeCardTb.TimeCardProjectID')
				->where(['TimeCardTb.TimeCardID' => $timeCardID]);
			$ptoData = $ptoQuery->one($db);
		}
		return $ptoData;
	}
	
	//updates pto records when PTO time entries are deactivated
	public static function updatePTO($timeEntryArray){
		//loop time entries
		foreach($timeEntryArray as $timeEntry){
			//calculate time entry quantity
			$timeEntryQuantity = self::calcFloatTimeDiff($timeEntry->TimeEntryStartTime, $timeEntry->TimeEntryEndTime);
			//find accompanying PTO records
			$ptoRecordArray = PTO::find()
				->where(['TimeCardID' => $timeEntry->TimeEntryTimeCardID])
				->andWhere(['and',
					['<=', 'StartDate', $timeEntry->TimeEntryStartTime],
					['>=', 'EndDate', $timeEntry->TimeEntryStartTime],
				])
				->all();
			//Loop PTO Records until time entry quantity has been accounted for
			foreach($ptoRecordArray as $ptoRecord){
				//save pto history record
				$ptoHistory = new PTOHistory;
				$ptoHistory->attributes = $ptoRecord->attributes;
				if($ptoHistory->save()){
					//grab current time entry qunatity to prevent overwrite issues
					$currentTimeEntryQunatity = $timeEntryQuantity;
					//compare PTO quantity to calc value  else delete
					//if pto > than time entry, update pto 
					if($ptoRecord->Quantity > $currentTimeEntryQunatity){
						//subtract pto quantity from time quantity
						$timeEntryQuantity -= $ptoRecord->Quantity;
						$ptoRecord->Quantity -= $currentTimeEntryQunatity;
						//call func to update mediator
						self::updateMediator($ptoRecord);
						if(!$ptoRecord->update())
							throw BaseActiveController::modelValidationException($ptoRecord);
					}
					//else pto is <=  time entry, delete pto record
					else{
						//subtract pto quantity from time quantity
						$timeEntryQuantity -= $ptoRecord->Quantity;
						$ptoRecord->Quantity -= $currentTimeEntryQunatity;
						//call func to update mediator
						self::updateMediator($ptoRecord);
						if(!$ptoRecord->delete())
							throw BaseActiveController::modelValidationException($ptoRecord);
					}
					//if timeEntryQuantity is <= 0 break out of loop
					if($timeEntryQuantity <= 0)
						break;
				}else{
					//ptoHistory failed to save
					throw BaseActiveController::modelValidationException($ptoHistory);
				}
			}
		}
	}
	
	//calculates the float value in hours of the difference between two date time strings
	//may move this to base controller if more uses are found
	private static function calcFloatTimeDiff($startDate, $endDate){
		$d1 = new DateTime($startDate);
		$d2 = new DateTime($endDate);
		$diff = $d2->diff($d1);
		return (float)((($diff->h*60) + $diff->i)/60.0);
	}
	
	//preform cascade update/deletes of pto mediator records
	private static function updateMediator($ptoRecord){
		//find PTOMediator by PTOID
		$ptoMediator = PTOMediator::find()
			->where(['PTOID' => $ptoRecord->ID])
			->andWhere(['or',
				['IS', 'IsInSync', null],
				['<>', 'IsInSync', 1]
			])
			->one();
		//get difference in qunatity to update pending balances
		$balanceChange = $ptoMediator->DeltaChange - $ptoRecord->Quantity;
		//if qunatity <0 than 0 delete record else update to new quantity
		if($ptoRecord->Quantity <= 0){
			if(!$ptoMediator->delete())
				throw BaseActiveController::modelValidationException($ptoMediator);
		}else{
			$ptoMediator->DeltaChange = $ptoRecord->Quantity;
			$ptoMediator->PendingBalance += $balanceChange;
			if(!$ptoMediator->update())
				throw BaseActiveController::modelValidationException($ptoMediator);
		}
		//find any subsequent PTOMediator records
		//find PTOMediator by PTOID
		$subsequentRecords = PTOMediator::find()
			->where(['UserID' => $ptoMediator->UserID])
			->andWhere(['or',
				['IS', 'IsInSync', null],
				['<>', 'IsInSync', 1]
			])
			->andWhere([ '>', 'DeltaTimeStamp', $ptoMediator->DeltaTimeStamp])
			->all();
		//loop and update PendingBalance for subsequent PTOMediator records
		foreach($subsequentRecords as $subsequentRecord){
			$subsequentRecord->PendingBalance += $balanceChange;
			if(!$subsequentRecord->update())
				throw BaseActiveController::modelValidationException($subsequentRecord);
		}
	}
}