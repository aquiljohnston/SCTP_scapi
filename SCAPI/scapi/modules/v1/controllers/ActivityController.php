<?php

namespace app\modules\v1\controllers;

use app\modules\v1\modules\pge\controllers\TaskOutController;
use Yii;
use app\modules\v1\models\Activity;
use app\modules\v1\models\TimeEntry;
use app\modules\v1\models\MileageEntry;
use app\modules\v1\models\SCUser;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\controllers\AssetAddressController;
use app\modules\v1\modules\pge\controllers\WindSpeedController;
use app\modules\v1\modules\pge\controllers\EquipmentController;
use app\modules\v1\modules\pge\controllers\WorkQueueController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * ActivityController implements the CRUD actions for the Activity model.
 */
class ActivityController extends BaseActiveController
{
    public $modelClass = 'app\modules\v1\models\Activity';
	
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
		// try
		// {
			//set db target
			$headers = getallheaders();
			Activity::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('activityCreate');
			
			//capture and decode the input json
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			$activityArray = $data["activity"];
			
			//create and format response json
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//handle $activityArray
			if ($activityArray != null)
			{
				//get number of activities
				$activitySize = count($activityArray);
				
				$createdBy = Parent::getUserFromToken()->UserUID;
				
				for($i = 0; $i < $activitySize; $i++)
				{
					$activity = new Activity();
					$clientActivity = new Activity();
					$activityArray[$i]["ActivityCreateDate"] = Parent::getDate();
					$activityArray[$i]["ActivityCreatedUserUID"] = $createdBy;
					//check array data
					$timeLength = 0;
					$mileageLength = 0;
					if ($activityArray[$i]["timeEntry"] != null)
					{
						$timeArray = $data["activity"][$i]["timeEntry"];
						//Get first and last time entry from timeArray and pass to ActivityStartTime and ActivityEndTime
						$timeLength = count($timeArray);
						$activityArray[$i]["ActivityStartTime"] = $timeArray[0]["TimeEntryStartTime"];
						$activityArray[$i]["ActivityEndTime"] = $timeArray[$timeLength-1]["TimeEntryEndTime"];
					}
					if ($activityArray[$i]["mileageEntry"] != null)
					{
						$mileageArray = $data["activity"][$i]["mileageEntry"];
						$mileageLength = count($mileageArray);
					}
					
					//load attributes to model
					$activity->attributes = $activityArray[$i];
					$clientActivity->attributes = $activity->attributes;
					
					Yii::trace("SC Activity: " . json_encode($activity->attributes));
					Yii::trace("Client Activity: " . json_encode($clientActivity->attributes));
					
					//save activity to ct
					if($activity->save())
					{
						//change db path to save on client db
						Activity::setClient($headers['X-Client']);
						$clientActivity->save();

						//convert the new activity back to an array so it can be loaded into the response
						$savedActivity= $activity->toArray();
						
						//update response json with new activity data
						$data["activity"][$i] = $savedActivity;
						
						//handle pge inspection
						if (array_key_exists("AssetAddress", $activityArray[$i]))
						{
							$savedAssetAddress = AssetAddressController::assetAddressParse($activityArray[$i]["AssetAddress"], $headers['X-Client'], $createdBy, $activity->ActivityUID);
							$data["activity"][$i]["AssetAddress"] = $savedAssetAddress;
						}

						//handle pge wind speed entries
						if (array_key_exists("WindSpeed", $activityArray[$i]))
						{
							$savedWindSpeed = WindSpeedController::create($activityArray[$i]["WindSpeed"], $headers['X-Client'], $createdBy);
							$data["activity"][$i]["WindSpeed"] = $savedWindSpeed;
						}
						
						//handle pge equipment calibration
						if (array_key_exists("EquipmentCalibration", $activityArray[$i]))
						{
							$savedEquipmentCalibrations = EquipmentController::calibrationParse($activityArray[$i]["EquipmentCalibration"], $headers['X-Client'], $createdBy);
							$data["activity"][$i]["EquipmentCalibration"] = $savedEquipmentCalibrations;
						}
						
						//handle pge lock work queue
						if (array_key_exists("WorkQueue", $activityArray[$i]))
						{
							$lockedWorkQueue = WorkQueueController::lockRecords($activityArray[$i]["WorkQueue"], $headers['X-Client'], $createdBy);
							$data["activity"][$i]["WorkQueue"] = $lockedWorkQueue;
						}


						if (array_key_exists('TaskOutMaps', $activityArray[$i])) {
						    Yii::trace("Array key TaskOutMaps Exists!");
						    TaskOutController::processJSON(json_encode($activityArray[$i]));
                        } else {
						    Yii::trace("Array key TaskOutMaps does not exist!");
                        }


						//change path back to ct db
						Activity::setClient(BaseActiveController::urlPrefix());
						$response->setStatusCode(201);
					
						//set up empty arrays
						$data["activity"][$i]["timeEntry"] = array();
						$data["activity"][$i]["mileageEntry"] = array();
						
						//add activityID to corresponding time entries
						if($timeLength > 0)
						{
							for($t = 0; $t < $timeLength; $t++)
							{
								$timeArray[$t]["TimeEntryActivityID"] = $data["activity"][$i]["ActivityID"];
								$timeEntry = new TimeEntry();
								$timeEntry->attributes = $timeArray[$t];
								$timeEntry->TimeEntryCreatedBy = $createdBy;
								$timeEntry->TimeEntryCreateDate = Parent::getDate();
								Yii::Trace("Client Activity: " . json_encode($timeEntry->attributes));
								if($timeEntry->save())
									{
										$response->setStatusCode(201);
										//update response json with new timeEntry data
										$data["activity"][$i]["timeEntry"][$t] = $timeEntry;
									}
								else
									{
										//throw a bad request if any save fails
										$response->setStatusCode(400);
										$response->data = "Http: 400 Bad Request - Failed to Save Time Entry";
										return $response;
									}
							}
						}
						//add activityID to corresponding mileage entries
						if($mileageLength > 0)
						{
							for($m = 0; $m < $mileageLength; $m++)
							{
								$mileageArray[$m]["MileageEntryActivityID"]= $$data["activity"][$i]["ActivityID"];
								$mileageEntry = new MileageEntry();
								$mileageEntry->attributes = $mileageArray[$m];
								$mileageEntry->MileageEntryCreatedBy = $createdBy;
								$mileageEntry->MileageEntryCreateDate = Parent::getDate();
								Yii::Trace("Client Activity: " . json_encode($mileageEntry->attributes));
								if($mileageEntry->save())
									{
										$response->setStatusCode(201);
										//update response json with new mileageEntry data
										$data["activity"][$i]["mileageEntry"][$m] = $mileageEntry;
									}
								else
									{
										//throw a bad request if any save fails
										$response->setStatusCode(400);
										$response->data = "Http:400 Bad Request - Failed to Save Mileage Entry";
										return $response;
									}
							}
						}
					} else {
					    Yii::trace("Could not validate the Activity");
                    }
				}
			}
			//build and return the response json
			$response->data = $data; 
			return $response;
		// }
		// catch(\Exception $e) 
		// {
			// throw new \yii\web\HttpException(400);
		// }
	}
}