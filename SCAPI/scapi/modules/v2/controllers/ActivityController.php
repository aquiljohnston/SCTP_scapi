<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\constants\Constants;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Activity;
use app\modules\v2\models\TimeCard;
use app\modules\v2\models\TimeEntry;
use app\modules\v3\models\MileageEntry;	
use app\modules\v2\models\MileageCard;	
use app\modules\v2\models\SCUser;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\WorkQueueController;
use app\modules\v2\controllers\EquipmentController;
use app\modules\v2\controllers\InspectionController;
use app\modules\v3\controllers\ExpenseController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\base\ErrorException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * ActivityController implements the CRUD actions for the Activity model.
 */
class ActivityController extends BaseActiveController
{
    public $modelClass = 'app\modules\v2\models\Activity';
	
	/**
	* sets verb filters for http request
	* @return an array of behaviors
	*/
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'create' => ['post'],
					'view' => ['get'],
					'add-time' => ['put'],
                ],  
            ];
		return $behaviors;	
	}
	
	/**
	 * Unsets the default actions so that we can override them
	 *
	 * @return array An array containing the parent's actions with some removed
	 */
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}

	/**
	 * Finds an Activity based on ID
	 * @param $id ID of the Activity to find
	 *
	 * @return Response JSON
	 * @throws \yii\web\HttpException
	 */
	public function actionView($id)
	{
		// RBAC permission check
		PermissionsController::requirePermission('activityView');
		
		try
		{
			//set db target
			Activity::setClient(BaseActiveController::urlPrefix());
			
			$activity = Activity::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $activity;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}

	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;

	/**
	 * Creates Activities from the contents of POST data.
	 * Creates MileageEntries and TimeEntries for each activity if provided.
	 *
	 * @return \yii\console\Response|Response
	 * @throws \yii\web\HttpException
	 */
	public function actionCreate($data = null)
	{		
		try
		{
			//set db target
			$headers = getallheaders();
			
			Activity::setClient(BaseActiveController::urlPrefix());
			//get user making the request
			$user = Parent::getUserFromToken();
			$createdBy = $user->UserName;
			$userID = $user->ID;
			
			// RBAC permission check
			PermissionsController::requirePermission('activityCreate');
			
			if($data == null)
			{
				//capture and decode the input json
				$post = file_get_contents("php://input");
				$data = json_decode(utf8_decode($post), true);
			}
			
			//Archive complete json array
			BaseActiveController::archiveJson(json_encode($data), 'ActivityJSON', $createdBy, $headers['X-Client']);
			
			//create and format response json
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$responseData = [];
			
			//handle activity data
			if ($data != null)
			{
				//get number of activities
				$activitySize = count($data['activity']);
				
				for($i = 0; $i < $activitySize; $i++)
				{
					//wrap individual activity in try catch for error logging
					try
					{
						//save json to archive
						BaseActiveController::archiveJson(json_encode($data['activity'][$i]), $data['activity'][$i]['ActivityTitle'], $createdBy, $headers['X-Client']);

						//handle app version from tablet TODO fix this later so it is consistent between web and tablet
						if(array_key_exists('AppVersion', $data['activity'][$i]))
						{
							$data['activity'][$i]['ActivityAppVersion'] = $data['activity'][$i]['AppVersion'];
						}
						if(array_key_exists('AppVersionName', $data['activity'][$i]))
						{
							$data['activity'][$i]['ActivityAppVersionName'] = $data['activity'][$i]['AppVersionName'];
						}
						//check array data
						$timeLength = 0;
						$mileageLength = 0;
						if ($data['activity'][$i]['timeEntry'] != null)
						{
							$timeArray = $data['activity'][$i]['timeEntry'];
							//Get first and last time entry from timeArray and pass to ActivityStartTime and ActivityEndTime
							$timeLength = count($timeArray);
							if(array_key_exists('TimeEntryStartTime', $timeArray[0]))
							{
								$data['activity'][$i]['ActivityStartTime'] = $timeArray[0]['TimeEntryStartTime'];
							}
							if(array_key_exists('TimeEntryEndTime', $timeArray[$timeLength-1]))
							{
								$data['activity'][$i]['ActivityEndTime'] = $timeArray[$timeLength-1]['TimeEntryEndTime'];
							}
						}
						if ($data['activity'][$i]['mileageEntry'] != null)
						{
							$mileageArray = $data['activity'][$i]['mileageEntry'];
							$mileageLength = count($mileageArray);
						}
						
						$data['activity'][$i]['ActivityCreateDate'] = Parent::getDate();
						
						//create base activity model and load data
						$activity = new Activity();
						$activity->attributes = $data['activity'][$i];
						
						//set created by
						$activity->ActivityCreatedUserUID = (string)$createdBy;
						
						//if client is not SCCT create client activity model and load data
						if(!BaseActiveController::isScct($headers['X-Client']))
						{
							$clientActivity = new Activity();
							$clientActivity->attributes = $data['activity'][$i];
							$clientActivity->ActivityCreatedUserUID = (string)$createdBy;
						}

						Activity::setClient(BaseActiveController::urlPrefix());
						//save activity to ct
						if($activity->save())
						{
							//change db path to save on client db
							Activity::setClient($headers['X-Client']);
							//save client activity and log error
							if(isset($clientActivity) && !$clientActivity->save())
							{
								$e = BaseActiveController::modelValidationException($clientActivity);
								BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data['activity'][$i]);
								
								//if activity id is not available try and get the record that should have saved
								if($clientActivity->ActivityID == null){
									$existingActivity = Activity::find()
										->where(['ActivityUID' => $data['activity'][$i]['ActivityUID']])
										->andWhere(['ActivitySrcDTLT' => $data['activity'][$i]['ActivitySrcDTLT']])
										->one();
									if($existingActivity != null){
										$clientActivity->ActivityID = $existingActivity->ActivityID;
									}else{
										//if no activity is found create a new activity and attempt to save again
										try{
											$reAttemptActivity = new Activity();
											$reAttemptActivity->attributes = $data['activity'][$i];
											$reAttemptActivity->ActivityCreatedUserUID = (string)$createdBy;
											if($reAttemptActivity->save()){
												$clientActivity->ActivityID = $reAttemptActivity->ActivityID;
											}else{
												$e = BaseActiveController::modelValidationException($clientActivity);
												BaseActiveController::archiveErrorJson('Activity Reattempt ' . file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data['activity'][$i]);
											}
										}catch(\Exception $e){
											BaseActiveController::archiveErrorJson('Activity Reattempt ' . file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data['activity'][$i]);
										}
									}
								}
							}

							//set success flag for activity
							$responseData['activity'][$i] = ['ActivityUID'=>$data['activity'][$i]['ActivityUID'], 'SuccessFlag'=>1];
							//client data parse
							if(BaseActiveController::isScct($headers['X-Client']))
							{
								$clientData = self::parseActivityData($data['activity'][$i], $headers['X-Client'],$createdBy, $activity->ActivityID);
							} else {
								$clientData = self::parseActivityData($data['activity'][$i], $headers['X-Client'],$createdBy, $clientActivity->ActivityID);
							}
							$responseData['activity'][$i] = array_merge($responseData['activity'][$i], $clientData);
						
							//change path back to ct db
							Activity::setClient(BaseActiveController::urlPrefix());
							$response->setStatusCode(201);
						
							//set up empty arrays
							$responseData['activity'][$i]['timeEntry'] = array();
							$responseData['activity'][$i]['mileageEntry'] = array();
							
							//redirect to base db for time and mileage processing
							BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
							TimeEntry::setClient(BaseActiveController::urlPrefix());
							MileageEntry::setClient(BaseActiveController::urlPrefix());
							
							//add activityID to corresponding time entries
							if($timeLength > 0){
								//create timeCardTransaction
								$db = BaseActiveRecord::getDb();
								$timeCardTransaction = $db->beginTransaction();
								for($t = 0; $t < $timeLength; $t++){
									$timeArray[$t]['TimeEntryActivityID'] = $activity->ActivityID;
									$timeEntry = new TimeEntry();
									$timeEntry->attributes = $timeArray[$t];
									$timeEntry->TimeEntryUserID = $userID;
									$timeEntry->TimeEntryCreatedBy = (string)$createdBy;
									try{
										//fetch current time card id if none is provided
										if($timeEntry->TimeEntryTimeCardID == null && $timeEntry->TimeEntryStartTime != null){
											//get last sunday
											$tcLastSunday = date('Y-m-d H:i:s', strtotime('last Sunday', strtotime($timeEntry->TimeEntryStartTime)));
											$timeCard = TimeCard::find()
											->where([
												'TimeCardTechID' => $userID,
												'TimeCardStartDate' => $tcLastSunday
											])
											->one();
											$timeEntry->TimeEntryTimeCardID = $timeCard->TimeCardID;
										}
										if($timeEntry->save()){
											$response->setStatusCode(201);
											//set success flag for time entry
											$responseData['activity'][$i]['timeEntry'][$t] = $timeEntry;
										}else{
											//log validation error
											$e = BaseActiveController::modelValidationException($timeEntry);
											//SQL Constraint
											if(strpos($e, TimeEntry::SQL_CONSTRAINT_MESSAGE)){
												//set success flag for time entry to success if validation was a sql constraint
												$responseData['activity'][$i]['timeEntry'][$t] = ['SuccessFlag'=>1];
											}else{
												BaseActiveController::archiveErrorJson(
													file_get_contents("php://input"),
													$e,
													getallheaders()['X-Client'],
													$data['activity'][$i],
													$data['activity'][$i]['timeEntry'][$t]);
												//set success flag for time entry
												$responseData['activity'][$i]['timeEntry'][$t] = ['SuccessFlag'=>0];
											}
										}
									}catch(yii\db\Exception $e){
										//return $e->errorInfo;
										//if db exception is 2601, duplicate contraint then success
										if(in_array($e->errorInfo[1], array(2601, 2627))){
											$responseData['activity'][$i]['timeEntry'][$t] = $timeEntry;
										}else{ 
											//log other errors and retrun failure
											BaseActiveController::archiveErrorJson(
												file_get_contents("php://input"),
												$e,
												getallheaders()['X-Client'],
												$data['activity'][$i],
												$data['activity'][$i]['timeEntry'][$t]);
											$responseData['activity'][$i]['timeEntry'][$t] = ['SuccessFlag'=>0];
										}
									}
								}
								$timeCardTransaction->commit();
							}
							
							//add activityID to corresponding mileage entries
							if($mileageLength > 0){	
								//create mileageCardTransaction
								$db = BaseActiveRecord::getDb();
								$mileageCardTransaction = $db->beginTransaction();
								for($m = 0; $m < $mileageLength; $m++){
									$mileageArray[$m]['MileageEntryActivityID']= $activity->ActivityID;
									$mileageEntry = new MileageEntry();
									$mileageEntry->attributes = $mileageArray[$m];
									$mileageEntry->MileageEntryCreatedBy = (string)$createdBy;
									try{
										//fetch current mileage card id if none is provided
										if($mileageEntry->MileageEntryMileageCardID == null && $mileageEntry->MileageEntryStartDate != null){
											//get last sunday
											$mcLastSunday = date('Y-m-d H:i:s', strtotime('last Sunday', strtotime($mileageEntry->MileageEntryStartDate)));
											$mileageCard = MileageCard::find()
											->where([
												'MileageCardTechID' => $userID,
												'MileageStartDate' => $mcLastSunday
											])
											->one();
											$mileageEntry->MileageEntryMileageCardID = $mileageCard->MileageCardID;
										}
										if($mileageEntry->save()){
											$response->setStatusCode(201);
											//set success flag for mileage entry
											$responseData['activity'][$i]['mileageEntry'][$m] = $mileageEntry;
										}else{
											//log validation error
											$e = BaseActiveController::modelValidationException($mileageEntry);
											BaseActiveController::archiveErrorJson(
												file_get_contents("php://input"),
												$e,
												getallheaders()['X-Client'],
												$data['activity'][$i],
												$data['activity'][$i]['mileageEntry'][$m]);
											//set success flag for mileage entry
											$responseData['activity'][$i]['mileageEntry'][$m] = ['SuccessFlag'=>0];
										}
									}catch(yii\db\Exception $e){
										//if db exception is 2601, duplicate contraint then success
										if(in_array($e->errorInfo[1], array(2601, 2627))){
											$responseData['activity'][$i]['mileageEntry'][$m] = $mileageEntry;
										}else{ 
											//log other errors and return failure
											BaseActiveController::archiveErrorJson(
												file_get_contents("php://input"),
												$e,
												getallheaders()['X-Client'],
												$data['activity'][$i],
												$data['activity'][$i]['mileageEntry'][$m]
												);
											$responseData['activity'][$i]['mileageEntry'][$m] = ['SuccessFlag'=>0];
										}
									}
								}
								$mileageCardTransaction->commit();
							}
						}else{
							//activity model validation exception
							throw BaseActiveController::modelValidationException($activity);
						}
					}catch(\Exception $e){
						//log activity error
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data['activity'][$i]);
						//set success flag for activity
						$responseData['activity'][$i] = ['ActivityUID'=>$data['activity'][$i]['ActivityUID'], 'SuccessFlag'=>0];
					}
				}
			}
			//build and return the response json
			$response->data = $responseData; 
			return $response;
		} catch(UnauthorizedHttpException $e) {
            throw new UnauthorizedHttpException;
        } catch(\Exception $e) {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
			throw new \yii\web\HttpException(400);
		}
	}
	
	//helper method, to parse activity data and send to appropriate controller.
	public static function parseActivityData($activityData, $client, $createdBy, $clientActivityID)
	{	
		$responseData = [];
	
		//handle accepting work queue
		if (array_key_exists('WorkQueue', $activityData)){
			$workQueueResponse = WorkQueueController::accept($activityData['WorkQueue'], $client);
			$responseData['WorkQueue'] = $workQueueResponse;
		}
		//handle creation of new calibration records
		if (array_key_exists('Calibration', $activityData)){
			$calibrationResponse = EquipmentController::processCalibration($activityData['Calibration'], $client, $clientActivityID);
			$responseData['Calibration'] = $calibrationResponse;
		}
		//handle creation of new inspection records
		if (array_key_exists('Inspection', $activityData)){
			$inspectionResponse = InspectionController::processInspection($activityData['Inspection'], $client, $clientActivityID);
			$responseData['Inspection'] = $inspectionResponse;
		}
		//handle creation of new task out records
		if (array_key_exists('TaskOut', $activityData)){
			$taskOutResponse = TaskOutController::processTaskOut($activityData['TaskOut'], $client, $clientActivityID);
			$responseData['TaskOut'] = $taskOutResponse;
		}
		//handle creation of new task out records
		if (array_key_exists('Expense', $activityData)){
			$expenseResponse = ExpenseController::processExpense($activityData['Expense'], $client);
			$responseData['Expense'] = $expenseResponse;
		}
		
		return $responseData;
	}
	
	public function actionAddTime()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//get user making the request
			$user = Parent::getUserFromToken();
			
			// RBAC permission check
			PermissionsController::requirePermission('activityCreate');
			
			//capture and decode the input json
			$put = file_get_contents("php://input");
			$data = json_decode($put, true)['activity'];
			
			//Archive complete json array
			BaseActiveController::archiveJson(json_encode($data), 'ActivityAddTime', $user->UserName, $headers['X-Client']);
			
			//get count of activities to add time to
			$activityCount = count($data);

			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			try{
				for($i = 0; $i<$activityCount; $i++)
				{
					$responseData['activity'][$i]['ActivityUID'] = $data[$i]['ActivityUID'];
					$responseData['activity'][$i]['timeEntry'] = self::saveTimeEntry($data[$i]['timeEntry'], $data[$i]['ActivityUID'], $user);
				}
			}catch(\Exception $e){
				//if an exception that cannot be handled gracefully occurs rollback to break transaction and rethrow to log
				$transaction->rollback();
				throw $e;
			}
			
			//commit transaction
			$transaction->commit();
			
			//create and format response json
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
			
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e) {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
			throw new \yii\web\HttpException(400);
		}
	}
	
	//TODO try and make use of this within the base activity save
	private function saveTimeEntry($timeArray, $activityUID, $user)
	{
		$resultArray = [];
		$timeLength = count($timeArray);
		
		//get activity id based on uid
		$activity = Activity::find()
			->select('ActivityID')
			->where(['ActivityUID' => $activityUID])
			->orderBy(['ActivityID' => SORT_DESC])
			->one();
		$activityID = $activity->ActivityID;
		
		for($t = 0; $t < $timeLength; $t++){
			Activity::setClient(BaseActiveController::urlPrefix());
			$timeArray[$t]['TimeEntryActivityID'] = $activityID;
			$timeEntry = new TimeEntry();
			$timeEntry->attributes = $timeArray[$t];
			$timeEntry->TimeEntryUserID = $user->ID;
			$timeEntry->TimeEntryCreatedBy = $user->UserName;
			try{
				if($timeEntry->save())
				{
					//set success flag for time entry
					$resultArray[$t] = $timeEntry;
				}
				else
				{
					//log validation error
					$e = BaseActiveController::modelValidationException($timeEntry);
					//SQL Constraint
					if(strpos($e, TimeEntry::SQL_CONSTRAINT_MESSAGE)){
						//set success flag for time entry to success if validation was a sql constraint
						$resultArray[$t] = ['SuccessFlag'=>1];
					} else {
						BaseActiveController::archiveErrorJson(
							file_get_contents("php://input"),
							$e,
							getallheaders()['X-Client'],
							$activityID,
							$timeArray[$t]);
						//set success flag for time entry
						$resultArray[$t] = ['SuccessFlag'=>0];
					}
				}
			}catch(yii\db\Exception $e){
				//if db exception is 2601, duplicate contraint then success
				if(in_array($e->errorInfo[1], array(2601, 2627)))
				{
					$resultArray[$t] = $timeEntry;
				}
				else //log other errors and return failure
				{
					BaseActiveController::archiveErrorJson(
						file_get_contents("php://input"),
						$e,
						getallheaders()['X-Client'],
						$activityID,
						$timeArray[$t]);
					$resultArray[$t] = ['SuccessFlag'=>0];
				}
			}
		}
		return $resultArray;
	}
}