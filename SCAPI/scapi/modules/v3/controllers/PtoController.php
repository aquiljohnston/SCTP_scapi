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
use app\modules\v3\models\PTOMediator;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\TimeEntry;
use app\modules\v3\models\SCUser;

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
					$transaction->rollback();
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
					$successFlag = 0;
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
		$ptoMediator->DeltaTimeStamp = $pto->SrcCreatedDateTime;
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
}