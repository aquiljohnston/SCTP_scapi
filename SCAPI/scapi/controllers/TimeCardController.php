<?php

namespace app\controllers;

use Yii;
use app\models\TimeCard;
use app\models\TimeEntry;
use app\models\SCUser;
use app\models\Project;
use app\models\ProjectUser;
use app\models\AllTimeCardsCurrentWeek;
use app\models\AllTimeCardsPriorWeek;
use app\models\AllApprovedTimeCardsCurrentWeek;
use app\models\AllUnapprovedTimeCardsCurrentWeek;
use app\models\TimeCardSumHoursWorkedCurrent;
use app\models\TimeCardSumHoursWorkedPrior;
use app\controllers\BaseActiveController;
use app\authentication\TokenAuth;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use \DateTime;

/**
 * TimeCardController implements the CRUD actions for TimeCard model.
 */
class TimeCardController extends BaseActiveController
{
	public $modelClass = 'app\models\TimeCard';
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['delete'],
					'update' => ['put'],
					'approve-time-cards'  => ['put'],
					'view-all-time-cards-current-week' => ['get'],
					'view-all-time-cards-prior-week' => ['get'],
					'view-all-approved-time-cards-current-week' => ['get'],
					'view-all-unapproved-time-cards-current-week' => ['get'],
					'view-time-card-hours-worked' => ['get'],
					'get-time-cards-current-week-by-manager' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionCreate()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
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
	
    public function actionView($id)
    {
		//set db target
		$headers = getallheaders();
		TimeCard::setClient($headers['X-Client']);
		
		$timeCard = TimeCard::findOne($id);
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $timeCard;
		
		return $response;
	}
	
	public function actionViewAllTimeCardsCurrentWeek()
	{
		//set db target
		$headers = getallheaders();
		AllTimeCardsCurrentWeek::setClient($headers['X-Client']);
		
		$timecardArray = AllTimeCardsCurrentWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewAllTimeCardsPriorWeek()
	{
		//set db target
		$headers = getallheaders();
		AllTimeCardsPriorWeek::setClient($headers['X-Client']);
		
		$timecardArray = AllTimeCardsPriorWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewAllApprovedTimeCardsCurrentWeek()
	{
		//set db target
		$headers = getallheaders();
		AllApprovedTimeCardsCurrentWeek::setClient($headers['X-Client']);
		
		$timecardArray = AllApprovedTimeCardsCurrentWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewAllUnapprovedTimeCardsCurrentWeek()
	{
		//set db target
		$headers = getallheaders();
		AllUnapprovedTimeCardsCurrentWeek::setClient($headers['X-Client']);
		
		$timecardArray = AllUnapprovedTimeCardsCurrentWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewTimeCardHoursWorkedCurrent()
	{
		//set db target
		$headers = getallheaders();
		TimeCardSumHoursWorkedCurrent::setClient($headers['X-Client']);
		
		$timecardArray = TimeCardSumHoursWorkedCurrent::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewTimeCardHoursWorkedPrior()
	{
		//set db target
		$headers = getallheaders();
		TimeCardSumHoursWorkedPrior::setClient($headers['X-Client']);
		
		$timecardArray = TimeCardSumHoursWorkedPrior::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionGetTimeCardCurrentWeek($id)
	{
		//set db target
		$headers = getallheaders();
		AllTimeCardsCurrentWeek::setClient($headers['X-Client']);
		
		$timeCard = AllTimeCardsCurrentWeek::findOne(['UserID'=>$id]);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		if ($timeCard != null)
		{
			$response->setStatusCode(200);
			$response->data = $timeCard;
			return $response;
		}
		else
		{
			$response->setStatusCode(404);
			return $response;
		}
	}
	
	public function actionViewTimeEntries($id)
	{
		//set db target
		$headers = getallheaders();
		TimeCard::setClient($headers['X-Client']);
		TimeEntry::setClient($headers['X-Client']);
		
		$response = Yii::$app ->response;
		$dataArray = [];
		$timeCard = TimeCard::findOne($id);
		$date = new DateTime($timeCard-> TimeCardStartDate);
		
		//get all time entries for Sunday
		$sundayDate = $date;
		$sundayStr = $sundayDate->format('Y-m-d H:i:s');
		$sundayEntries = TimeEntry::find()
			->where("TimeEntryDate ="."'"."$sundayStr". "'")
			->andWhere("TimeEntryTimeCardID = $id")
			->all();
		
		//get all time entries for Monday
		$mondayDate = $date->modify('+1 day');	
		$mondayStr = $mondayDate->format('Y-m-d H:i:s');		
		$mondayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$mondayStr". "'")
			->andWhere("TimeEntryTimeCardID = $id")
			->all();
			
		//get all time entries for Tuesday	
		$tuesdayDate = $date->modify('+1 day');
		$tuesdayStr = $tuesdayDate->format('Y-m-d H:i:s');
		$tuesdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$tuesdayStr". "'")
			->andWhere("TimeEntryTimeCardID = $id")
			->all();
			
		//get all time entries for Wednesday	
		$wednesdayDate = $date->modify('+1 day');
		$wednesdayStr = $wednesdayDate->format('Y-m-d H:i:s');
		$wednesdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$wednesdayStr". "'")
			->andWhere("TimeEntryTimeCardID = $id")
			->all();
			
		//get all time entries for Thursday
		$thursdayDate = $date->modify('+1 day');
		$thursdayStr = $thursdayDate->format('Y-m-d H:i:s');
 		$thursdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$thursdayStr". "'")
			->andWhere("TimeEntryTimeCardID = $id")
			->all();
			
		//get all time entries for Friday
		$fridayDate = $date->modify('+1 day');
		$fridayStr = $fridayDate->format('Y-m-d H:i:s');
		$fridayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$fridayStr". "'")
			->andWhere("TimeEntryTimeCardID = $id")
			->all();
			
		//get all time entries for Saturday
		$satudayDate = $date->modify('1 day');
		$satudayStr = $satudayDate->format('Y-m-d H:i:s');
		$saturdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$satudayStr". "'")
			->andWhere("TimeEntryTimeCardID = $id")
			->all();
			
		//load data into array
		$dataArray["StartDate"] = $timeCard-> TimeCardStartDate;
		$dataArray["EndDate"] = $timeCard-> TimeCardEndDate;
		$dayArray =
		[
			"Sunday" => $sundayEntries,
			"Monday" => $mondayEntries,
			"Tuesday" => $tuesdayEntries,
			"Wednesday" => $wednesdayEntries,
			"Thursday" => $thursdayEntries,
			"Friday" => $fridayEntries,
			"Saturday" => $saturdayEntries,
		];
		$dataArray["TimeEntries"] = [$dayArray];
		
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $dataArray;
	}
	
	public function actionApproveTimeCards()
	{
		//set db target
		$headers = getallheaders();
		TimeCard::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		
		//capture put body
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);
		
		//create response
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//parse json
		$approvedBy = $data["approvedByID"];
		$cardIDs = $data["cardIDArray"];
		
		//get timecards
		foreach($cardIDs as $id)
		{
			$approvedCards[]= TimeCard::findOne($id);
		}
		
		//get user's name by ID
		if ($user = SCUser::findOne(['UserID'=>$approvedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$approvedBy = $lname.", ".$fname;
		}
		
		//try to approve time cards
		try
		{
			//create transaction
			$connection = \Yii::$app->db;
			$transaction = $connection->beginTransaction(); 
		
			foreach($approvedCards as $card)
			{
				$card-> TimeCardApprovedFlag = "Yes";
				$card-> TimeCardApprovedBy = $approvedBy;
				$card-> update();
			}
			$transaction->commit();
			$response->setStatusCode(200);
			$response->data = $approvedCards; 
			return $response;
		}
		//if transaction fails rollback changes and send error
		catch(Exception $e)
		{
			$transaction->rollBack();
			$response->setStatusCode(400);
			$response->data = "Http:400 Bad Request";
			return $response;
			
		}
	}
	
	// function to get all timecards for the current week associated with a project manager
	public function actionGetTimeCardsCurrentWeekByManager($userID)
	{
		//set db target
		$headers = getallheaders();
		AllTimeCardsCurrentWeek::setClient($headers['X-Client']);
		ProjectUser::setClient($headers['X-Client']);
		
		//get all projects for manager
		$projects = ProjectUser::find()
			->where("ProjUserUserID = $userID")
			->all();
		$projectsSize = count($projects);
		
		$timeCards = [];
		
		//get all users associated with projects
		for($i = 0; $i < $projectsSize; $i++)
		{
			$projectID = $projects[$i]->ProjUserProjectID; 
			$newUsers = ProjectUser::find()
				->where("ProjUserProjectID = $projectID")
				->all();
				
			//get project name for array key
			$project = Project::find()
				->where("ProjectID = $projectID")
				->one();
			$projectName = $project->ProjectName;
			
			//pass users to project key
			$timeCards[$projectName] = $newUsers;
			$newUsersSize = count($newUsers);
			
			//get mileage card information
			for($j = 0; $j < $newUsersSize; $j++)
			{
				$userID = $timeCards[$projectName][$j]->ProjUserUserID;
				$timeCards[$projectName][$j] = AllTimeCardsCurrentWeek::find()
					->where("UserID = $userID")
					->one();
			}
		}
		
		// //get all users associated with projects
		// for($i = 0; $i < $projectsSize; $i++)
		// {
			// $projectID = $projects[$i]->ProjUserProjectID; 
			// $newUsers = ProjectUser::find()
				// ->where("ProjUserProjectID = $projectID")
				// ->all();
			// $users = array_merge($users, $newUsers);
		// }
		// $usersSize = count($users);
		
		// //get all mileage cards for current week for users
		// for($i = 0; $i < $usersSize; $i++)
		// {
			// $userID = $users[$i]->ProjUserUserID;
			// $newCards = AllTimeCardsCurrentWeek::find()
				// ->where("UserID = $userID")
				// ->all();
			// $timeCards = array_unique(array_merge($timeCards, $newCards), SORT_REGULAR);
		// }
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->setStatusCode(200);
		$response->data = $timeCards;
		return $response;
	}
	
}
