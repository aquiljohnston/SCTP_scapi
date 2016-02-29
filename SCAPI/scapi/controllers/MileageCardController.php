<?php

namespace app\controllers;

use Yii;
use app\models\MileageCard;
use app\models\MileageEntry;
use app\models\SCUser;
use app\models\AllMileageCardsCurrentWeek;
use app\models\AllMileageCardsPriorWeek;
use app\models\AllApprovedMileageCardsCurrentWeek;
use app\models\AllUnApprovedMileageCardsCurrentWeek;
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
 * MileageCardController implements the CRUD actions for MileageCard model.
 */
class MileageCardController extends BaseActiveController
{
    public $modelClass = 'app\models\MileageCard'; 
	
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
                    'delete' => ['delete'],
					'update' => ['put'],
					'approve-mileage-cards'  => ['put'],
					'view-all-mileage-cards-current-week' => ['get'],
					'view-all-mileage-cards-prior-week' => ['get'],
					'view-all-approved-mileage-cards-current-week' => ['get'],
					'view-all-unapproved-mileage-cards-current-week' => ['get'],
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

    /**
     * Displays a single MileageCard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $mileageCard = MileageCard::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $mileageCard;
		
		return $response;
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
	
	public function actionViewAllMileageCardsCurrentWeek()
	{
		$mileagecardArray = AllMileageCardsCurrentWeek::find()->all();
		$mileagecardData = array_map(function ($model) {return $model->attributes;},$mileagecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $mileagecardData;
	}
	
	public function actionViewAllMileageCardsPriorWeek()
	{
		$mileagecardArray = AllMileageCardsPriorWeek::find()->all();
		$mileagecardData = array_map(function ($model) {return $model->attributes;},$mileagecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $mileagecardData;
	}
	
	public function actionViewAllApprovedMileageCardsCurrentWeek()
	{
		$mileagecardArray = AllApprovedMileageCardsCurrentWeek::find()->all();
		$mileagecardData = array_map(function ($model) {return $model->attributes;},$mileagecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $mileagecardData;
	}
	
	public function actionViewAllUnapprovedMileageCardsCurrentWeek()
	{
		$mileagecardArray = AllUnApprovedMileageCardsCurrentWeek::find()->all();
		$mileagecardData = array_map(function ($model) {return $model->attributes;},$mileagecardArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $mileagecardData;
	}

	public function actionViewMileageEntries($id)
	{
		$response = Yii::$app ->response;
		$dataArray = [];
		$mileageCard = MileageCard::findOne($id);
		$date = new DateTime($mileageCard-> MileageStartDate);
		
		//get all time entries for Sunday
		$sundayDate = $date;
		$sundayStr = $sundayDate->format('Y-m-d H:i:s');
		$sundayEntries = MileageEntry::find()
			->where("MileageEntryDate ="."'"."$sundayStr". "'")
			->andWhere("MileageEntryMileageCardID = $id")
			->all();
		
		//get all time entries for Monday
		$mondayDate = $date->modify('+1 day');	
		$mondayStr = $mondayDate->format('Y-m-d H:i:s');		
		$mondayEntries =MileageEntry::find()
			->where("MileageEntryDate ="."'"."$mondayStr". "'")
			->andWhere("MileageEntryMileageCardID = $id")
			->all();
			
		//get all time entries for Tuesday	
		$tuesdayDate = $date->modify('+1 day');
		$tuesdayStr = $tuesdayDate->format('Y-m-d H:i:s');
		$tuesdayEntries =MileageEntry::find()
			->where("MileageEntryDate ="."'"."$tuesdayStr". "'")
			->andWhere("MileageEntryMileageCardID = $id")
			->all();
			
		//get all time entries for Wednesday	
		$wednesdayDate = $date->modify('+1 day');
		$wednesdayStr = $wednesdayDate->format('Y-m-d H:i:s');
		$wednesdayEntries =MileageEntry::find()
			->where("MileageEntryDate ="."'"."$wednesdayStr". "'")
			->andWhere("MileageEntryMileageCardID = $id")
			->all();
			
		//get all time entries for Thursday
		$thursdayDate = $date->modify('+1 day');
		$thursdayStr = $thursdayDate->format('Y-m-d H:i:s');
 		$thursdayEntries =MileageEntry::find()
			->where("MileageEntryDate ="."'"."$thursdayStr". "'")
			->andWhere("MileageEntryMileageCardID = $id")
			->all();
			
		//get all time entries for Friday
		$fridayDate = $date->modify('+1 day');
		$fridayStr = $fridayDate->format('Y-m-d H:i:s');
		$fridayEntries =MileageEntry::find()
			->where("MileageEntryDate ="."'"."$fridayStr". "'")
			->andWhere("MileageEntryMileageCardID = $id")
			->all();
			
		//get all time entries for Saturday
		$satudayDate = $date->modify('1 day');
		$satudayStr = $satudayDate->format('Y-m-d H:i:s');
		$saturdayEntries =MileageEntry::find()
			->where("MileageEntryDate ="."'"."$satudayStr". "'")
			->andWhere("MileageEntryMileageCardID = $id")
			->all();
			
		//load data into array
		$dataArray["StartDate"] = $mileageCard-> MileageStartDate;
		$dataArray["EndDate"] = $mileageCard-> MileageEndDate;
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
		$dataArray["MileageEntries"] = [$dayArray];
		
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $dataArray;
	}
	
	public function actionApproveMileageCards()
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
			$approvedCards[]= MileageCard::findOne($id);
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
				$card-> MileageCardApprovedFlag = "yes";
				$card-> MileageCardApprovedBy = $approvedBy;
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
