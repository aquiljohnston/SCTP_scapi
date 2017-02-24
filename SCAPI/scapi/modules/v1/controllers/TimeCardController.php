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
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use \DateTime;
use yii\data\Pagination;

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
		try
		{
			//set db target
			TimeCard::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeCardView');
			
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
			TimeCard::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeCardApproveCards');
			
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
		try
		{
			//set db target
			TimeCard::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeCardGetEntries');
			
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
		try
		{
			//get http headers
			$headers = getallheaders();
			//set db target
			AllTimeCardsCurrentWeek::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeCardGetCard');
			
			//get project based on header
			$project = Project::find()
				->where(['ProjectUrlPrefix'=>$headers['X-Client']])
				->one();
			
			//get time card
			$timeCardQuery = AllTimeCardsCurrentWeek::find()
				->where(['UserID'=>$userID]);
			if($project != null)
			{
				$timeCardQuery->andwhere(['TimeCardProjectID'=>$project->ProjectID]);
			}
			$timeCard = $timeCardQuery->one();
			
			//handle response
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
	
	public function actionGetCards($week, $listPerPage = null, $page = null)
	{
		// RBAC permission check is embedded in this action	
		try
		{
			//set db target headers
			$headers = getallheaders();
			TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::setClient(BaseActiveController::urlPrefix());
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//response array of time cards
            $timeCardsArr = [];
            $responseArray = [];
			
			//rbac permission check
			if (PermissionsController::can('timeCardGetAllCards'))
			{
				//check if week is prior or current to determine appropriate view
				if($week == 'prior')
				{
					$timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::find();
                    $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
                    $timeCardsArr = $paginationResponse['Query']->orderBy('UserID,TimeCardStartDate,ProjectID')->all();
                    $responseArray['assets'] = $timeCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
                    //$timeCardArray = array_map(function ($model) {return $model->attributes;},$timeCardsArr);
                }
            elseif($week == 'current')
				{
					$timeCards = TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::find();
                    $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
                    $timeCardsArr = $paginationResponse['Query']->orderBy('UserID,TimeCardStartDate,ProjectID')->all();
                    $responseArray['assets'] = $timeCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
                    //$timeCardArray = array_map(function ($model) {return $model->attributes;},$timeCards);
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
				if($week == 'prior' && $projectsSize > 0)
				{
                    $timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectNameNew::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);

                    for($i=0; $i < $projectsSize; $i++)
                    {
                        $projectID = $projects[$i]->ProjUserProjectID;
                        $timeCards->andWhere(['ProjectID'=>$projectID]);
                    }
                    $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
                    $timeCardsArr = $paginationResponse['Query']->orderBy('UserID,TimeCardStartDate,ProjectID')->all();
                    $responseArray['assets'] = $timeCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];

				} 
				elseif($week == 'current' && $projectsSize > 0)
				{
                    $timeCards = TimeCardSumHoursWorkedCurrentWeekWithProjectNameNew::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
					for($i=0; $i < $projectsSize; $i++)
					{
						$projectID = $projects[$i]->ProjUserProjectID;
                        $timeCards->andWhere(['ProjectID'=>$projectID]);
                    }
                    $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
                    $timeCardsArr = $paginationResponse['Query']->orderBy('UserID,TimeCardStartDate,ProjectID')->all();
                    $responseArray['assets'] = $timeCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
				}
			}
			else{
				throw new ForbiddenHttpException;
			}

			if (!empty($responseArray['assets']))
			{
				$response->data = $responseArray;
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

	public function paginationProcessor($assetQuery, $page, $listPerPage){

        if($page != null)
        {
            // set pagination
            $countAssetQuery = clone $assetQuery;
            $pages = new Pagination(['totalCount' => $countAssetQuery->count()]);
            $pages->pageSizeLimit = [1,100];
            $offset = $listPerPage*($page-1);
            $pages->setPageSize($listPerPage);
            $pages->pageParam = 'timeCardPage';
            $pages->params = ['per-page' => $listPerPage, 'timeCardPage' => $page];

            $assetQuery->offset($offset)
                ->limit($listPerPage);

            $asset['pages'] = $pages;
            $asset['Query'] = $assetQuery;

            return $asset;
        }
    }
}
