<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\TimeCard;
use app\modules\v1\models\TimeEntry;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\Project;
use app\modules\v1\models\ProjectUser;
use app\modules\v1\models\AllTimeCardsCurrentWeek;
use app\modules\v1\models\TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew;
use app\modules\v1\models\TimeCardSumHoursWorkedPriorWeekWithProjectNameNew;
use app\modules\v1\controllers\BaseActiveController;
use app\authentication\TokenAuth;
// use app\modules\v1\authentication\TokenAuth;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
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
	public $modelClass = 'app\modules\v1\models\TimeCard';
	
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
	
	use CreateMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;

	/**
	 * Displays a single TimeCard model.
	 * @param integer $id
	 * @return mixed
	 * @throws \yii\web\HttpException
	 */
    public function actionView($id)
    {
		// RBAC permission check
		PermissionsController::requirePermission('timeCardView');

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
		// RBAC permission check
		PermissionsController::requirePermission('timeCardApproveCards');
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
			
			//get userid
			$approvedBy = self::getUserFromToken()->UserID;
			
			//parse json
			$cardIDs = $data["cardIDArray"];
			
			//get timecards
			foreach($cardIDs as $id)
			{
				$approvedCards[]= TimeCard::findOne($id);
			}
			
			//try to approve time cards
			try {
				//create transaction
				$connection = \Yii::$app->db;
				$transaction = $connection->beginTransaction();

				foreach ($approvedCards as $card) {
					$card->TimeCardApprovedFlag = "Yes";
					$card->TimeCardApprovedBy = $approvedBy;
					$card->update();
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $approvedCards;
				return $response;
			} catch (ForbiddenHttpException $e) {
				throw new ForbiddenHttpException;
			}
			catch(\Exception $e) //if transaction fails rollback changes and send error
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
		// RBAC permission check
		PermissionsController::requirePermission('timeCardGetEntries');
		
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
			$mondayStr = $mondayDate->format(BaseActiveController::DATE_FORMAT);
			$mondayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$mondayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Tuesday	
			$tuesdayDate = $date->modify('+1 day');
			$tuesdayStr = $tuesdayDate->format(BaseActiveController::DATE_FORMAT);
			$tuesdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$tuesdayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Wednesday	
			$wednesdayDate = $date->modify('+1 day');
			$wednesdayStr = $wednesdayDate->format(BaseActiveController::DATE_FORMAT);
			$wednesdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$wednesdayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Thursday
			$thursdayDate = $date->modify('+1 day');
			$thursdayStr = $thursdayDate->format(BaseActiveController::DATE_FORMAT);
			$thursdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$thursdayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Friday
			$fridayDate = $date->modify('+1 day');
			$fridayStr = $fridayDate->format(BaseActiveController::DATE_FORMAT);
			$fridayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$fridayStr". "'")
				->andWhere("TimeEntryTimeCardID = $cardID")
				->all();
				
			//get all time entries for Saturday
			$saturdayDate = $date->modify('1 day');
			$saturdayStr = $saturdayDate->format(BaseActiveController::DATE_FORMAT);
			$saturdayEntries =TimeEntry::find()
				->where("TimeEntryDate ="."'"."$saturdayStr". "'")
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
		// RBAC permission check
		PermissionsController::requirePermission('timeCardGetCard');
		
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
	
	public function actionGetCards($week)
	{
		// RBAC permission check is embedded in this action	
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
			
			//rbac permission check
			if (PermissionsController::can('timeCardGetAllCards'))
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
			//rbac permission check	
			elseif (PermissionsController::can('timeCardGetOwnCards'))	
			{
				$userID = self::getUserFromToken()->UserID;
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
			else{
				throw new ForbiddenHttpException;
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
		catch(ForbiddenHttpException $e) {
			throw $e;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
