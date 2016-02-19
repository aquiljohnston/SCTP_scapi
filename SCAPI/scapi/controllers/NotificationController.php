<?php

namespace app\controllers;

use Yii;
use app\authentication\TokenAuth;
use app\models\SCUser;
use app\models\Project;
use app\models\ProjectUser;
use app\models\GetEquipmentByClientProjectVw;
use app\models\AllTimeCardsPriorWeek;
use app\models\AllMileageCardsPriorWeek;
use app\controllers\BaseActiveController;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Link;
use yii\db\mssql\PDO;


/**
 * UserController implements the CRUD actions for User model.
 */
class NotificationController extends Controller
{	
	 public function behaviors()
    {
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-notifications' => ['get']
                ],  
            ];
		return $behaviors;		
	}
	
	public function actionGetNotifications($userID)
	{
		//get user
		$user = SCUser::findOne($userID);
		
		//get projects the user belongs to
		$projectData = $user->projects;
		$projectArray = array_map(function ($model) {return $model->attributes;},$projectData);
		$projectSize = count($projectArray);
		
		//load data into array
		$notifications = [];
		$notifications["firstName"] = $user-> UserFirstName;
		$notifications["lastName"] = $user-> UserLastName;
		$notifications["equipment"] = []; 
		$notifications["timeCards"] = []; 
		$notifications["mileageCards"] = []; 
		$equipmentTotal = 0;
		$timeCardTotal = 0;
		$mileageCardTotal = 0;
			
		//loop projects to get data
		for($i=0; $i < $projectSize; $i++)
		{		
			$projectID = $projectArray[$i]["ProjectID"];
			$projectName =  $projectArray[$i]["ProjectName"];
			
			//get unaccepted equipment for project
			$equipment = GetEquipmentByClientProjectVw::find()
				->where(['and', "ProjectID = $projectID","[Accepted Flag] = 0"])
				->all();
			$equipmentCount = count($equipment);

			//get unapproved time cards from last week for project
			$timeCards = AllTimeCardsPriorWeek::find()
				->where(['and', "TimeCardProjectID = $projectID","TimeCardApproved = 'No'"])
				->all();
			$timeCardCount = count($timeCards);
			
			//get unapproved mileage cards from last week for project
			$mileageCards = AllMileageCardsPriorWeek::find()
				->where(['and', "MileageCardProjectID = $projectID","MileageCardApprove = 'No'"])
				->all();	
			$mileageCardCount = count($mileageCards);
			
			$equipmentData["Project"]= $projectName;
			$equipmentData["Total"]= $equipmentCount;
			
			$timeCardData["Project"]= $projectName;
			$timeCardData["Total"]= $timeCardCount;
			
			$mileageCardData["Project"]= $projectName;
			$mileageCardData["Total"]= $mileageCardCount;

			$notifications["equipment"][] = $equipmentData;
			$notifications["timeCards"][] = $timeCardData;
			$notifications["mileageCards"][] = $mileageCardData;
			
			$equipmentTotal += $equipmentCount;
			$timeCardTotal += $timeCardCount;
			$mileageCardTotal += $mileageCardCount;
		}
		
		$equipmentData["Project"]= "Total";
		$equipmentData["Total"]= $equipmentTotal;
		
		$timeCardData["Project"]= "Total";
		$timeCardData["Total"]= $timeCardTotal;
		
		$mileageCardData["Project"]= "Total";
		$mileageCardData["Total"]= $mileageCardTotal;
		
		$notifications["equipment"][] = $equipmentData;
		$notifications["timeCards"][] = $timeCardData;
		$notifications["mileageCards"][] = $mileageCardData;
		
		
		//send response
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $notifications;
		return $response;
	}

}