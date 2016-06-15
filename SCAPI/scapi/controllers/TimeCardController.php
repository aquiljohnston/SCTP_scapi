<?php

namespace app\controllers;

use Yii;
use app\models\TimeCard;
use app\models\TimeEntry;
use app\models\SCUser;
use app\models\Project;
use app\models\ProjectUser;
use app\models\AllTimeCardsCurrentWeek;
use app\models\TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew;
use app\models\TimeCardSumHoursWorkedPriorWeekWithProjectNameNew;
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
					'view-time-card-hours-worked-current' => ['get'],
					'view-time-card-hours-worked-prior' => ['get'],
					'view-all-by-user-by-project-current' => ['get'],
					'view-all-by-user-by-project-prior' => ['get'],
					'view-time-entries' => ['get'],
					'get-time-card-current-week' => ['get'],
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
		try
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
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionViewTimeCardHoursWorkedCurrent()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::setClient($headers['X-Client']);

			$timecardArray = TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::find()->
			orderBy('UserID,TimeCardStartDate,ProjectID')->all();

			$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $timecardData;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionViewTimeCardHoursWorkedPrior()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::setClient($headers['X-Client']);
			
			$timecardArray = TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::find()->
			orderBy('UserID,TimeCardStartDate,ProjectID')->all();

			$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $timecardData;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionGetTimeCardCurrentWeek($id)
	{
		try
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionViewTimeEntries($id)
	{
		try
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
			$dataArray["ApprovedFlag"] = $timeCard-> TimeCardApprovedFlag;
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionApproveTimeCards()
	{
		try
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	//returns a json containing all timecards for projects that a user is associated with for the current week
	//used by proj managers and supervisors
	public function actionViewAllByUserByProjectCurrent($userID)
	{
		try{
			//set db target
			$headers = getallheaders();
			TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//get user project relations array
			$projects = ProjectUser::find()
				->where("ProjUserUserID = $userID")
				->all();
			$projectsSize = count($projects);
			
			//response array of time cards
			$timeCardArray = [];
			
			//loop user project array get all time cards WHERE equipmentProjectID is equal
			for($i=0; $i < $projectsSize; $i++)
			{
				$projectID = $projects[$i]->ProjUserProjectID; 
				
				$timeCards = TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::find()
				->where(['ProjectID' => $projectID])
				->orderBy('UserID,TimeCardStartDate,ProjectID')
				->all();
				$timeCardArray = array_merge($timeCardArray, $timeCards);
			}
			
			$response->data = $timeCardArray;
			$response->setStatusCode(200);
			return $response;
			
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	//returns a json containing all timecards for projects that a user is associated with for the prior week
	//used by proj managers and supervisors	
	public function actionViewAllByUserByProjectPrior($userID)
	{
		try{
			//set db target
			$headers = getallheaders();
			TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//get user project relations array
			$projects = ProjectUser::find()
				->where("ProjUserUserID = $userID")
				->all();
			$projectsSize = count($projects);
			
			//response array of time cards
			$timeCardArray = [];
			
			//loop user project array get all time cards WHERE equipmentProjectID is equal
			for($i=0; $i < $projectsSize; $i++)
			{
				$projectID = $projects[$i]->ProjUserProjectID; 
				
				$timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::find()
				->where(['ProjectID' => $projectID])
				->orderBy('UserID,TimeCardStartDate,ProjectID')
				->all();
				$timeCardArray = array_merge($timeCardArray, $timeCards);
			}
			
			$response->data = $timeCardArray;
			$response->setStatusCode(200);
			return $response;
			
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
}
