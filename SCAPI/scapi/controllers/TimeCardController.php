<?php

namespace app\controllers;

use Yii;
use app\models\TimeCard;
use app\models\TimeEntry;
use app\models\SCUser;
use app\models\AllTimeCardsCurrentWeek;
use app\models\AllTimeCardsPriorWeek;
use app\models\AllApprovedTimeCardsCurrentWeek;
use app\models\AllUnapprovedTimeCardsCurrentWeek;
use app\controllers\BaseActiveController;
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
		return [
			'verbs' => [
				'class' => \yii\filters\VerbFilter::className(),
				'actions' => [
					'approve-time-cards'  => ['put'],
					'view-all-time-cards-current-week' => ['get'],
					'view-all-time-cards-prior-week' => ['get'],
					'view-all-approved-time-cards-current-week' => ['get'],
					'view-all-unapproved-time-cards-current-week' => ['get'],
				],
			],
		];
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
	
    public function actionView($id)
    {
		$timeCard = TimeCard::findOne($id);
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $timeCard;
		
		return $response;
	}
	
	public function actionViewAllTimeCardsCurrentWeek()
	{
		$timecardArray = AllTimeCardsCurrentWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewAllTimeCardsPriorWeek()
	{
		$timecardArray = AllTimeCardsPriorWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewAllApprovedTimeCardsCurrentWeek()
	{
		$timecardArray = AllApprovedTimeCardsCurrentWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionViewAllUnapprovedTimeCardsCurrentWeek()
	{
		$timecardArray = AllUnapprovedTimeCardsCurrentWeek::find()->all();
		$timecardData = array_map(function ($model) {return $model->attributes;},$timecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timecardData;
	}
	
	public function actionGetTimeCardCurrentWeek($id)
	{
		$timeCard = AllTimeCardsCurrentWeek::findOne(['UserID'=>$id]);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timeCard;
	}
	
	public function actionViewTimeEntries($id)
	{
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
				$card-> TimeCardApprovedFlag = 1;
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
	
}
