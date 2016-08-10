<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\Activity;
use app\modules\v1\models\TimeEntry;
use app\modules\v1\models\MileageEntry;
use app\modules\v1\models\SCUser;
use app\modules\v1\controllers\BaseActiveController;
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
			$headers = getallheaders();
			Activity::setClient($headers['X-Client']);
			
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
		// RBAC permission check
		PermissionsController::requirePermission('activityCreate');
		
		try
		{
			//set db target
			$headers = getallheaders();
			Activity::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			TimeEntry::setClient($headers['X-Client']);
			MileageEntry::setClient($headers['X-Client']);
			
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
				
				for($i = 0; $i < $activitySize; $i++)
				{
					$activity = new Activity();
					$activityArray[$i]["ActivityCreateDate"] = Parent::getDate();
					$activityArray[$i]["ActivityCreatedUserUID"] = Parent::getUserFromToken()->UserID;
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
					
					//save activity 
					if($activity->save())
					{
						$response->setStatusCode(201);
						//convert the new activity back to an array so it can be loaded into the response
						$savedActivity= $activity->toArray();
						$activityArray[$i] = $savedActivity;
						
						//update response json with new activity data
						$data["activity"][$i] = $activityArray[$i];
					
						//set up empty arrays
						$data["activity"][$i]["timeEntry"] = array();
						$data["activity"][$i]["mileageEntry"] = array();
						
						//add activityID to corresponding time entries
						if($timeLength > 0)
						{
							for($t = 0; $t < $timeLength; $t++)
							{
								$timeArray[$t]["TimeEntryActivityID"] = $activityArray[$i]["ActivityID"];
								$timeEntry = new TimeEntry();
								$timeEntry->attributes = $timeArray[$t];
								$timeEntry->TimeEntryCreatedBy = $activityArray[$i]["ActivityCreatedUserUID"];
								$timeEntry->TimeEntryCreateDate = Parent::getDate();
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
										$response->data = "Http:400 Bad Request";
										return $response;
									}
							}
						}
						//add activityID to corresponding mileage entries
						if($mileageLength > 0)
						{
							for($m = 0; $m < $mileageLength; $m++)
							{
								$mileageArray[$m]["MileageEntryActivityID"]= $activityArray[$i]["ActivityID"];
								$mileageEntry = new MileageEntry();
								$mileageEntry->attributes = $mileageArray[$m];
								$mileageEntry->MileageEntryCreatedBy = $activityArray[$i]["ActivityCreatedUserUID"];
								$mileageEntry->MileageEntryCreateDate = Parent::getDate();
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
										$response->data = "Http:400 Bad Request";
										return $response;
									}
							}
						}
					}
				}
			}
			//build and return the response json
			$response->data = $data; 
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}