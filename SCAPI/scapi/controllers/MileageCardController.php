<?php

namespace app\controllers;

use Yii;
use app\models\MileageCard;
use app\models\MileageEntry;
use app\models\SCUser;
use app\models\Project;
use app\models\ProjectUser;
use app\models\AllMileageCardsCurrentWeek;
use app\models\MileageCardSumMilesCurrentWeekWithProjectNameNew;
use app\models\MileageCardSumMilesPriorWeekWithProjectNameNew;
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
	
	use CreateMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	 /**
     * Displays a single MileageCard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		try
		{
			//set db target
			$headers = getallheaders();
			MileageCard::setClient($headers['X-Client']);
			
			$mileageCard = MileageCard::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $mileageCard;
			
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
			MileageCard::setClient($headers['X-Client']);
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
					$card-> MileageCardApprovedFlag = "Yes";
					$card-> MileageCardApprovedBy = $approvedBy;
					$card-> MileageCardModifiedDate = Parent::getDate();
					$card-> MileageCardModifiedBy = $approvedBy;
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
			MileageCard::setClient($headers['X-Client']);
			MileageEntry::setClient($headers['X-Client']);
			
			$response = Yii::$app ->response;
			$dataArray = [];
			$mileageCard = MileageCard::findOne($cardID);
			$date = new DateTime($mileageCard-> MileageStartDate);
			
			//get all time entries for Sunday
			$sundayDate = $date;
			$sundayStr = $sundayDate->format('Y-m-d H:i:s');
			$sundayEntries = MileageEntry::find()
				->where("MileageEntryDate ="."'"."$sundayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
			
			//get all time entries for Monday
			$mondayDate = $date->modify('+1 day');	
			$mondayStr = $mondayDate->format('Y-m-d H:i:s');		
			$mondayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$mondayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Tuesday	
			$tuesdayDate = $date->modify('+1 day');
			$tuesdayStr = $tuesdayDate->format('Y-m-d H:i:s');
			$tuesdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$tuesdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Wednesday	
			$wednesdayDate = $date->modify('+1 day');
			$wednesdayStr = $wednesdayDate->format('Y-m-d H:i:s');
			$wednesdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$wednesdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Thursday
			$thursdayDate = $date->modify('+1 day');
			$thursdayStr = $thursdayDate->format('Y-m-d H:i:s');
			$thursdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$thursdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Friday
			$fridayDate = $date->modify('+1 day');
			$fridayStr = $fridayDate->format('Y-m-d H:i:s');
			$fridayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$fridayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Saturday
			$satudayDate = $date->modify('1 day');
			$satudayStr = $satudayDate->format('Y-m-d H:i:s');
			$saturdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$satudayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//load data into array
			$dataArray["StartDate"] = $mileageCard-> MileageStartDate;
			$dataArray["EndDate"] = $mileageCard-> MileageEndDate;
			$dataArray["ApprovedFlag"] = $mileageCard-> MileageCardApprovedFlag;
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
			AllMileageCardsCurrentWeek::setClient($headers['X-Client']);
			
			$mileageCard = AllMileageCardsCurrentWeek::findOne(['UserID'=>$userID]);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			if ($mileageCard != null)
			{
				$response->setStatusCode(200);
				$response->data = $mileageCard;
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
			MileageCardSumMilesCurrentWeekWithProjectNameNew::setClient($headers['X-Client']);
			MileageCardSumMilesPriorWeekWithProjectNameNew::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//response array of mileage cards
			$mileageCardArray = [];
			
			//check if user is admin, admins will not limited by project
			if($isAdmin == "true")
			{
				//check if week is prior or current to determine appropriate view
				if($week == 'prior')
				{
					$mileageCards = MileageCardSumMilesPriorWeekWithProjectNameNew::find()
						->orderBy('UserID,MileageStartDate,ProjectID')
						->all();
						
					$mileageCardArray = array_map(function ($model) {return $model->attributes;},$mileageCards);
				} 
				elseif($week == 'current') 
				{
					$mileageCards = MileageCardSumMilesCurrentWeekWithProjectNameNew::find()
						->orderBy('UserID,MileageStartDate,ProjectID')
						->all();
						
					$mileageCardArray = array_map(function ($model) {return $model->attributes;},$mileageCards);
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
							
						$mileageCards = MileageCardSumMilesPriorWeekWithProjectNameNew::find()
							->where(['ProjectID' => $projectID])
							->orderBy('UserID,MileageStartDate,ProjectID')
							->all();
						$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
					}
				} 
				elseif($week == 'current') 
				{
					for($i=0; $i < $projectsSize; $i++)
					{
						$projectID = $projects[$i]->ProjUserProjectID; 
						
						$mileageCards = MileageCardSumMilesCurrentWeekWithProjectNameNew::find()
							->where(['ProjectID' => $projectID])
							->orderBy('UserID,MileageStartDate,ProjectID')
							->all();
						$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
					}
				}
			}
			if (!empty($mileageCardArray))
			{
				$response->data = $mileageCardArray;
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
