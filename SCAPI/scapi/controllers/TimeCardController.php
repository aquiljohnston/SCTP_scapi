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
					'create' => ['create'],
                    'delete' => ['delete'],
					'update' => ['put'],
					'view' => ['get'],
					'approve-cards'  => ['put'],
					'get-entries' => ['get'],
					'get-card' => ['get'],
					'get-cards' => ['get'],
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
	
		 /**
     * Displays a single TimeCard model.
     * @param integer $id
     * @return mixed
     */
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
	
	public function actionApproveCards()
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
	
	public function actionGetEntries($cardID)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			TimeCard::setClient($headers['X-Client']);
			TimeEntry::setClient($headers['X-Client']);
			
			$response = Yii::$app ->response;
			$dataArray = [];
			$timeCard = TimeCard::findOne($cardID);
			$date = new DateTime($timeCard-> TimeCardStartDate);
			
			//get all time entries for Sunday
			$sundayDate = $date;
			$sundayStr = $sundayDate->format('Y-m-d H:i:s');
			$sundayEntries = TimeEntry::find()
				->where("TimeEntryDate ="."'"."$sundayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
			
			//get all time entries for Monday
			$mondayDate = $date->modify('+1 day');	
			$mondayStr = $mondayDate->format('Y-m-d H:i:s');		
			$mondayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$mondayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Tuesday	
			$tuesdayDate = $date->modify('+1 day');
			$tuesdayStr = $tuesdayDate->format('Y-m-d H:i:s');
			$tuesdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$tuesdayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Wednesday	
			$wednesdayDate = $date->modify('+1 day');
			$wednesdayStr = $wednesdayDate->format('Y-m-d H:i:s');
			$wednesdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$wednesdayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Thursday
			$thursdayDate = $date->modify('+1 day');
			$thursdayStr = $thursdayDate->format('Y-m-d H:i:s');
			$thursdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$thursdayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Friday
			$fridayDate = $date->modify('+1 day');
			$fridayStr = $fridayDate->format('Y-m-d H:i:s');
			$fridayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$fridayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Saturday
			$satudayDate = $date->modify('1 day');
			$satudayStr = $satudayDate->format('Y-m-d H:i:s');
			$saturdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$satudayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
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
	
	public function actionGetCard($userID)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			AllTimeCardsCurrentWeek::setClient($headers['X-Client']);
			
			$timeCard = AllTimeCardsCurrentWeek::findOne(['UserID'=>$userID]);
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
	
	public function actionGetCards($userID, $isAdmin, $week)
	{
		try
		{
			//set db target headers
			$headers = getallheaders();
			TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::setClient($headers['X-Client']);
			TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//response array of time cards
			$timeCardArray = [];
			
			//check if user is admin, admins will not limited by project
			if($isAdmin == "true")
			{
				//check if week is prior or current to determine appropriate view
				if($week == 'prior')
				{
					$timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::find()
						->orderBy('UserID,TimeCardStartDate,ProjectID')
						->all();

					$timeCardArray = array_map(function ($model) {return $model->attributes;},$timeCards);
				} 
				elseif($week == 'current') 
				{
					$timeCards = TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::find()->
					orderBy('UserID,TimeCardStartDate,ProjectID')->all();

					$timeCardArray = array_map(function ($model) {return $model->attributes;},$timeCards);
				}
			} 
			//non-admin users will have their results filtered by associated projects	
			else		
			{
				//get user project relations array
				$projects = ProjectUser::find()
					->where("ProjUserUserID = $userID")
					->all();
				$projectsSize = count($projects);

				//check if week is prior or current to determine appropriate view
				if($week == 'prior')
				{
					for($i=0; $i < $projectsSize; $i++)
					{
						$projectID = $projects[$i]->ProjUserProjectID; 
						
						$timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::find()
							->where(['ProjectID' => $projectID])
							->orderBy('UserID,TimeCardStartDate,ProjectID')
							->all();
						$timeCardArray = array_merge($timeCardArray, $timeCards);
					}
				} 
				elseif($week == 'current') 
				{
					for($i=0; $i < $projectsSize; $i++)
					{
						$projectID = $projects[$i]->ProjUserProjectID; 
						
						$timeCards = TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::find()
							->where(['ProjectID' => $projectID])
							->orderBy('UserID,TimeCardStartDate,ProjectID')
							->all();
						$timeCardArray = array_merge($timeCardArray, $timeCards);
					}
				}
			}
			if (!empty($timeCardArray))
			{
				$response->data = $timeCardArray;
				$response->setStatusCode(200);
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
}
