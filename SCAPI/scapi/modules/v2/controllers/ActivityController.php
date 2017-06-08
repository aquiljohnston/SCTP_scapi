<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Activity;
use app\modules\v2\models\TimeEntry;
use app\modules\v2\models\MileageEntry;
use app\modules\v2\models\SCUser;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\WorkQueueController;
use app\modules\v2\controllers\EquipmentController;
use app\modules\v2\modules\pge\controllers\PgeActivityController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
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
	public function actionCreate()
	{		
		try
		{
			//set db target
			$headers = getallheaders();
			//get id on client db of user making request
			$clientCreatedBy = BaseActiveController::getClientUser($headers['X-Client'])->UserID;
			
			Activity::setClient(BaseActiveController::urlPrefix());
			//get uid of user making request
			$pgeCreatedBy = Parent::getUserFromToken()->UserUID;
			//get id of user making request
			$createdBy = Parent::getUserFromToken()->UserID;
			
			// RBAC permission check
			PermissionsController::requirePermission('activityCreate');

			//capture and decode the input json
			$post = file_get_contents("php://input");
			$data = json_decode(utf8_decode($post), true);
			
			//create and format response json
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$responseData = [];
			
			//handle activity data
			if ($data != null)
			{
				//get number of activities
				$activitySize = count($data);
				
				for($i = 0; $i < $activitySize; $i++)
				{
					//wrap individual activity in try catch for error logging
					try
					{
						//save json to archive
						if($headers['X-Client'] == BaseActiveRecord::PGE_DEV || $headers['X-Client'] == BaseActiveRecord::PGE_STAGE ||$headers['X-Client'] == BaseActiveRecord::PGE_PROD)
						{
							BaseActiveController::archiveJson(json_encode($data['activity'][$i]), $data['activity'][$i]['ActivityTitle'], $pgeCreatedBy, $headers['X-Client']);
						}
						else
						{
							BaseActiveController::archiveJson(json_encode($data['activity'][$i]), $data['activity'][$i]['ActivityTitle'], $createdBy, $headers['X-Client']);
						}
						
						$activity = new Activity();
						$clientActivity = new Activity();
						$data['activity'][$i]['ActivityCreateDate'] = Parent::getDate();
						$data['activity'][$i]['ActivityCreatedUserUID'] = $pgeCreatedBy;
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
							$data['activity'][$i]['ActivityStartTime'] = $timeArray[0]['TimeEntryStartTime'];
							$data['activity'][$i]['ActivityEndTime'] = $timeArray[$timeLength-1]['TimeEntryEndTime'];
						}
						if ($data['activity'][$i]['mileageEntry'] != null)
						{
							$mileageArray = $data['activity'][$i]['mileageEntry'];
							$mileageLength = count($mileageArray);
						}
						
						//load attributes to model
						$activity->attributes = $data['activity'][$i];
						$clientActivity->attributes = $activity->attributes;
						
						Activity::setClient(BaseActiveController::urlPrefix());
						//save activity to ct
						if($activity->save())
						{
							//change db path to save on client db
							Activity::setClient($headers['X-Client']);
							//save client activity and log error
							//$clientActivity->save();
							if(!$clientActivity->save())
							{
								$e = BaseActiveController::modelValidationException($clientActivity);
								BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data['activity'][$i]);
							}
							
							//Sends activity to client specific parse routine to check for additional client specific activity data
							//based on client header
							//check for pge headers, pge is handled uniquely compared to a standard client
							if($headers['X-Client'] == BaseActiveRecord::PGE_DEV || $headers['X-Client'] == BaseActiveRecord::PGE_STAGE ||$headers['X-Client'] == BaseActiveRecord::PGE_PROD)
							{
								//set success flag for activity
								$responseData['activity'][$i] = ['ActivityUID'=>$data['activity'][$i]['ActivityUID'], 'SuccessFlag'=>1];
								//pge data parse
								$clientData = PgeActivityController::parseActivityData($data['activity'][$i], $headers['X-Client'],$pgeCreatedBy, $activity->ActivityUID);
								$responseData['activity'][$i] = array_merge($responseData['activity'][$i], $clientData);
							}
							else
							{
								//set success flag for activity
								$responseData['activity'][$i] = ['ActivityUID'=>$data['activity'][$i]['ActivityUID'], 'SuccessFlag'=>1];
								//client data parse
								$clientData = self::parseActivityData($data['activity'][$i], $headers['X-Client'],$clientCreatedBy, $clientActivity->ActivityID);
								$responseData['activity'][$i] = array_merge($responseData['activity'][$i], $clientData);
							}
							
							//change path back to ct db
							Activity::setClient(BaseActiveController::urlPrefix());
							$response->setStatusCode(201);
						
							//set up empty arrays
							$responseData['activity'][$i]['timeEntry'] = array();
							$responseData['activity'][$i]['mileageEntry'] = array();
							
							//add activityID to corresponding time entries
							if($timeLength > 0)
							{
								for($t = 0; $t < $timeLength; $t++)
								{
									$timeArray[$t]['TimeEntryActivityID'] = $activity->ActivityID;
									$timeEntry = new TimeEntry();
									$timeEntry->attributes = $timeArray[$t];
									$timeEntry->TimeEntryCreatedBy = (string)$createdBy;
									$timeEntry->TimeEntryCreateDate = Parent::getDate();
									try{
										if($timeEntry->save())
										{
											$response->setStatusCode(201);
											//set success flag for time entry
											$responseData['activity'][$i]['timeEntry'][$t] = $timeEntry;
										}
										else
										{
											//log validation error
											$e = BaseActiveController::modelValidationException($timeEntry);
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
									catch(yii\db\Exception $e)
									{
										//return $e->errorInfo;
										//if db exception is 2601, duplicate contraint then success
										if(in_array($e->errorInfo[1], array(2601, 2627)))
										{
											$responseData['activity'][$i]['timeEntry'][$t] = $timeEntry;
										}
										else //log other errors and retrun failure
										{
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
							}
													
							
							//add activityID to corresponding mileage entries
							if($mileageLength > 0)
							{
								for($m = 0; $m < $mileageLength; $m++)
								{
									$mileageArray[$m]['MileageEntryActivityID']= $activity->ActivityID;
									$mileageEntry = new MileageEntry();
									$mileageEntry->attributes = $mileageArray[$m];
									$mileageEntry->MileageEntryCreatedBy = (string)$createdBy;
									$mileageEntry->MileageEntryCreateDate = Parent::getDate();
									try{
										if($mileageEntry->save())
										{
											$response->setStatusCode(201);
											//set success flag for mileage entry
											$responseData['activity'][$i]['mileageEntry'][$m] = $mileageEntry;
										}
										else
										{
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
									}
									catch(yii\db\Exception $e)
									{
										//if db exception is 2601, duplicate contraint then success
										if(in_array($e->errorInfo[1], array(2601, 2627)))
										{
											$responseData['activity'][$i]['mileageEntry'][$m] = $mileageEntry;
										}
										else //log other errors and retrun failure
										{
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
							}
						}
						else
						{
							//activiy model validation exception
							throw BaseActiveController::modelValidationException($activity);
						}
					}
					catch(\Exception $e)
					{
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
		}
		catch(\Exception $e) 
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
			throw new \yii\web\HttpException(400);
		}
	}
	
	//helper method, to parse activity data and send to appropriate controller.
	public static function parseActivityData($activityData, $client, $clientCreatedBy, $clientActivityID)
	{	
		//handle accepting work queue
		if (array_key_exists('WorkQueue', $activityData))
		{
			$workQueueResponse = WorkQueueController::accept($activityData['WorkQueue'], $client, $clientCreatedBy);
			$responseData['WorkQueue'] = $workQueueResponse;
		}
		//handle creation of new calibration records
		if (array_key_exists('Calibration', $activityData))
		{
			$calibrationResponse = EquipmentController::processCalibration($activityData['Calibration'], $client, $clientCreatedBy, $clientActivityID);
			$responseData['Calibration'] = $calibrationResponse;
		}
		
		return $responseData;
	}
}