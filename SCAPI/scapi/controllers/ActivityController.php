<?php

namespace app\controllers;

use Yii;
use app\models\Activity;
use app\models\TimeEntry;
use app\models\MileageEntry;
use app\models\SCUser;
use app\controllers\BaseActiveController;
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
    public $modelClass = 'app\models\Activity'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionView($id)
	{
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
	
	public function actionUpdate()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}

	public function actionCreate()
	{
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
			
			//parse the individual arrays of the input json
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
						
						//add activityID to corrosponding time entries
						if($timeLength > 0)
						{
							for($t = 0; $t < $timeLength; $t++)
							{
								$timeArray[$t]["TimeEntryActivityID"] = $activityArray[$i]["ActivityID"];
								$timeEntry = new TimeEntry();
								$timeEntry->attributes = $timeArray[$t];
								$timeEntry-> TimeEntryCreatedBy = $activityArray[$i]["ActivityCreatedBy"];
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
						//add activityID to corrosponding mileage entries
						if($mileageLength > 0)
						{
							for($m = 0; $m < $mileageLength; $m++)
							{
								$mileageArray[$m]["MileageEntryActivityID"]= $activityArray[$i]["ActivityID"];
								$mileageEntry = new MileageEntry();
								$mileageEntry->attributes = $mileageArray[$m];
								$mileageEntry-> MileageEntryCreatedBy = $activityArray[$i]["ActivityCreatedBy"];
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