<?php

namespace app\controllers;

use Yii;
use app\models\Activity;
use app\models\TimeEntry;
use app\models\MileageEntry;
use app\authentication\BaseActiveController;
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
		return $actions;
	}
	
	public function actionView($id)
	{
		$activity = Activity::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $activity;
		
		return $response;
	}

	public function actionCreate()
	{
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
			$activity = new Activity();
			$activitySize = count($activityArray);
			
			for($i = 0; $i < $activitySize; $i++)
			{
				$activity->attributes = $activityArray[$i];
				if ($data["activity"][$i]["timeEntry"] != null)
				{
					$timeArray = $data["activity"][$i]["timeEntry"][0];
				}
				if ($data["activity"][$i]["mileageEntry"] != null)
				{
					$mileageArray = $data["activity"][$i]["mileageEntry"][0];
				}
				
				if($activity->save())
				{
					$response->setStatusCode(201);
					//convert the new activity back to an array so it can be loaded into the response
					$savedActivity= $activity->toArray();
					$data["activity"][$i] = $savedActivity;
					
					//add activityID to corrosponding time entries
					if($data["activity"][$i]["ActivityTitle"] == "timeEntry")
					{
						$timeArray["TimeEntryActivityID"] = $data["activity"][$i]["ActivityID"];
						$timeEntry = new TimeEntry();
						$timeEntry->attributes = $timeArray;
						if($timeEntry->save())
							{
								$response->setStatusCode(201);
								//update response json with new timeEntry data
								$data["activity"][$i]["timeEntry"] = $timeEntry;
								$data["activity"][$i]["mileageEntry"] = array();
							}
						else
							{
								//throw a bad request if any save fails
								$response->setStatusCode(400);
								$response->data = "Http:400 Bad Request";
								return $response;
							}
					}
					//add activityID to corrosponding mileage entries
					if($data["activity"][$i]["ActivityTitle"] == "mileageEntry")
					{
						$mileageArray["MileageEntryActivityID"] = $data["activity"][$i]["ActivityID"];
						$mileageEntry = new MileageEntry();
						$mileageEntry->attributes = $mileageArray;
						if($mileageEntry->save())
						{
							$response->setStatusCode(201);
							//update response json with new mileageEntry data
							$data["activity"][$i]["timeEntry"] = array();
							$data["activity"][$i]["mileageEntry"] = $mileageEntry;
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
				//update response json with new activity data
				//$data["activity"][$i] = $activity;
			}
		}
		//build and return the response json
		$response ->data = $data; 
		return $response;
	}
}