<?php

namespace app\controllers;

use Yii;
use app\models\TimeCard;
use app\models\TimeEntry;
use app\controllers\BaseActiveController;
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
			->all();
		
		//get all time entries for Monday
		$mondayDate = $date->modify('+1 day');	
		$mondayStr = $mondayDate->format('Y-m-d H:i:s');		
		$mondayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$mondayStr". "'")
			->all();
			
		//get all time entries for Tuesday	
		$tuesdayDate = $date->modify('+1 day');
		$tuesdayStr = $tuesdayDate->format('Y-m-d H:i:s');
		$tuesdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$tuesdayStr". "'")
			->all();
			
		//get all time entries for Wednesday	
		$wednesdayDate = $date->modify('+1 day');
		$wednesdayStr = $wednesdayDate->format('Y-m-d H:i:s');
		$wednesdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$wednesdayStr". "'")
			->all();
			
		//get all time entries for Thursday
		$thursdayDate = $date->modify('+1 day');
		$thursdayStr = $thursdayDate->format('Y-m-d H:i:s');
 		$thursdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$thursdayStr". "'")
			->all();
			
		//get all time entries for Friday
		$fridayDate = $date->modify('+1 day');
		$fridayStr = $fridayDate->format('Y-m-d H:i:s');
		$fridayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$fridayStr". "'")
			->all();
			
		//get all time entries for Saturday
		$satudayDate = $date->modify('1 day');
		$satudayStr = $satudayDate->format('Y-m-d H:i:s');
		$saturdayEntries =TimeEntry::find()
			->where("TimeEntryDate ="."'"."$satudayStr". "'")
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
	
}
