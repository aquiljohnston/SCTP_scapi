<?php

namespace app\modules\v1\controllers;

use Yii;
use app\authentication\TokenAuth;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\Project;
use app\modules\v1\models\ProjectUser;
use app\modules\v1\models\GetEquipmentByClientProjectVw;
use app\modules\v1\models\TimeCardSumHoursWorkedPriorWeekWithProjectName;
use app\modules\v1\models\MileageCardSumMilesPriorWeekWithProjectName;
use app\modules\v1\controllers\BaseActiveController;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Link;
use yii\db\mssql\PDO;
use yii\base\ErrorException;
use yii\db\Exception;


/**
 * NotificationController creates user notifications.
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
	
	public function actionGetNotifications()
	{
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			//get user
			$userID = BaseActiveController::getUserFromToken()->UserID;
			$user = SCUser::findOne($userID);
			
			// check if login user is Engineer
			if($user->UserAppRoleType != "Engineer"){
				
				PermissionsController::requirePermission('notificationsGet');
				
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
						->where(['and', "ProjectID = $projectID","[Accepted Flag] = 'No'"])
						->orWhere(['and', "ProjectID = $projectID","[Accepted Flag] = 'Pending'"])
						->all();
					$equipmentCount = count($equipment);
					
					//get unapproved time cards from last week for project
					$timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectName::find()
						->where(['and', "TimeCardProjectID = $projectID","TimeCardApprovedFlag = 'No'"])
						->all();
					$timeCardCount = count($timeCards);
					
					//get unapproved mileage cards from last week for project
					$mileageCards = MileageCardSumMilesPriorWeekWithProjectName::find()
						->where(['and', "MileageCardProjectID = $projectID","MileageCardApprovedFlag = 'No'"])
						->all();	
					$mileageCardCount = count($mileageCards);
					
					//pass equipment data for project
					$equipmentData["Project"]= $projectName;
					$equipmentData["Number of Items"]= $equipmentCount;
					
					//pass time card data for project
					$timeCardData["Project"]= $projectName;
					$timeCardData["Number of Items"]= $timeCardCount;
					
					//pass mileage card data for project
					$mileageCardData["Project"]= $projectName;
					$mileageCardData["Number of Items"]= $mileageCardCount;
					
					//appened data to response array
					$notifications["equipment"][] = $equipmentData;
					$notifications["timeCards"][] = $timeCardData;
					$notifications["mileageCards"][] = $mileageCardData;

					//increment total counts
					$equipmentTotal += $equipmentCount;
					$timeCardTotal += $timeCardCount;
					$mileageCardTotal += $mileageCardCount;
				}
				
				//pass equipment data for total
				$equipmentData["Project"]= "Total";
				$equipmentData["Number of Items"]= $equipmentTotal;
				
				//pass time card data for total
				$timeCardData["Project"]= "Total";
				$timeCardData["Number of Items"]= $timeCardTotal;
				
				//pass mileage card data for total
				$mileageCardData["Project"]= "Total";
				$mileageCardData["Number of Items"]= $mileageCardTotal;
				
				//append totals to response array
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
		catch(ForbiddenHttpException $e)
		{
			throw new ForbiddenHttpException;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}