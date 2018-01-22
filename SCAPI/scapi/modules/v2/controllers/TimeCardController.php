<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\models\TimeCard;
use app\modules\v2\models\TimeEntry;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\Project;
use app\modules\v2\models\ProjectUser;
use app\modules\v2\models\AllTimeCardsCurrentWeek;
use app\modules\v2\models\TimeCardSumHoursWorkedCurrentWeekWithProjectName;
use app\modules\v2\models\TimeCardSumHoursWorkedPriorWeekWithProjectName;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\authentication\TokenAuth;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use \DateTime;
use yii\data\Pagination;
use yii\db\Query;

/**
 * TimeCardController implements the CRUD actions for TimeCard model.
 */
class TimeCardController extends BaseActiveController
{
	public $modelClass = 'app\modules\v2\models\TimeCard';
	
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
					'show-entries' => ['get'],
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
			$approvedBy = self::getUserFromToken()->UserName;
			
			//parse json
			$cardIDs = $data["cardIDArray"];
			$approvedCards = []; // Prevents empty array from causing crash
			//get timecards
			foreach($cardIDs as $id)
			{
				$approvedCards[]= TimeCard::findOne($id);
			}
			
			//try to approve time cards
			try {
				//create transaction
				$connection = TimeCard::getDb();
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
			
			$response = Yii::$app->response;
			$dataArray = [];
			$timeCard = TimeCard::findOne($cardID);
			
			$dayArray =
			[
				'Sunday' => ['Entries' => [], 'Total' => 0],
				'Monday' => ['Entries' => [], 'Total' => 0],
				'Tuesday' => ['Entries' => [], 'Total' => 0],
				'Wednesday' => ['Entries' => [], 'Total' => 0],
				'Thursday' => ['Entries' => [], 'Total' => 0],
				'Friday' => ['Entries' => [], 'Total' => 0],
				'Saturday' => ['Entries' => [], 'Total' => 0],
			];
			
			$entriesQuery = new Query;
			$entriesQuery->select('*')
					->from("fnTimeCardEntrysByTimeCard(:cardID)")
					->addParams([':cardID' => $cardID])
					->orderBy('TimeEntryStartTime');
			$entries = $entriesQuery->all(BaseActiveRecord::getDb());
			
			foreach ($entries as $entry)
			{
				$dayArray[$entry['TimeEntryWeekDay']]['Entries'][] = $entry;
			}
			foreach ($dayArray as $day => $data)
			{
				if(count($dayArray[$day]['Entries']) > 0)
				{
					$dayArray[$day]['Total'] = $data['Entries'][0]['DayTotalTime'];
				}
			}
				
			//load data into array
			$dataArray['StartDate'] = $timeCard-> TimeCardStartDate;
			$dataArray['EndDate'] = $timeCard-> TimeCardEndDate;
			$dataArray['ApprovedFlag'] = $timeCard-> TimeCardApprovedFlag;
			$dataArray['TimeEntries'] = $dayArray;
			
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $dataArray;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}	





	public function actionShowEntries($cardID)
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
			

			
			$entriesQuery = new Query;
			$entriesQuery->select('*')
					->from("fnTimeEntrysByTimeCard(:cardID)")
					->addParams([':cardID' => $cardID]);
			$entries = $entriesQuery->all(BaseActiveRecord::getDb());
			
			
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $entries;
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

    public function actionGetCards($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null)
    {
        $weekParameterIsInvalidString = "The acceptable values for week are 'prior' and 'current'";
        // RBAC permission check is embedded in this action
        try
        {
            //get headers
            $headers = getallheaders();
            //get client header
            $client = $headers['X-Client'];

            //url decode filter value
            $filter = urldecode($filter);

            //set db target headers
            $headers = getallheaders();
            TimeCardSumHoursWorkedCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;

            //response array of time cards
            $timeCardsArr = [];
            $responseArray = [];

            //build base query
            $timeCards = new Query;
            $timeCards->select('*')
                ->from(["fnTimeCardByDate(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate]);


            //if is scct website get all or own
            if(BaseActiveController::isSCCT($client))
            {
                /*
                 * Check if user can get all cards
                 */
                if (!PermissionsController::can('timeCardGetAllCards') && PermissionsController::can('timeCardGetOwnCards'))
                {
                    $userID = self::getUserFromToken()->UserID;
                    //get user project relations array
                    $projects = ProjectUser::find()
                        ->where("ProjUserUserID = $userID")
                        ->all();
                    $projectsSize = count($projects);

                    //check if week is prior or current to determine appropriate view
                    if($projectsSize > 0)
                    {
                        $timeCards->where(['TimeCardProjectID' => $projects[0]->ProjUserProjectID]);
                    }
                    if($projectsSize > 1)
                    {
                        for($i=1; $i < $projectsSize; $i++)
                        {
                            $projectID = $projects[$i]->ProjUserProjectID;
                            $timeCards->orWhere(['TimeCardProjectID'=>$projectID]);
                        }
                    }
                }
                /*
                 * Check if user can get their own cards
                 */
                elseif (!PermissionsController::can('timeCardGetAllCards'))
                {
                    throw new ForbiddenHttpException;
                }
            }
            else // get only cards for the current project.
            {
                //get project based on client header
                $project = Project::find()
                    ->where(['ProjectUrlPrefix' => $client])
                    ->one();
                //add project where to query
                $timeCards->where(['TimeCardProjectID' => $project->ProjectID]);
            }

            if($filter!= null && isset($timeCards)) { //Empty strings or nulls will result in false
                $timeCards->andFilterWhere([
                    'or',
                    //['like', 'UserName', $filter],
                    ['like', 'UserFirstName', $filter],
                    ['like', 'UserLastName', $filter],
                    ['like', 'ProjectName', $filter],
                    ['like', 'TimeCardApprovedFlag', $filter]
                    // TODO: Add TimeCardTechID -> name and username to DB view and add to filtered fields
                ]);
            }
            $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
            $timeCardsArr = $paginationResponse['Query']->orderBy('UserID,TimeCardStartDate,TimeCardProjectID')->all(BaseActiveRecord::getDb());
            $responseArray['assets'] = $timeCardsArr;
            $responseArray['pages'] = $paginationResponse['pages'];

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

    public function actionGetTimeCardsHistoryData($selectedTimeCardIDs = [], $week = null)
    {
        // RBAC permission check is embedded in this action
        try{
            //set db target headers
            TimeCardSumHoursWorkedCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;

            //response array of time cards
            $timeCardsArr = [];
            $selectedTimeCardIDs = json_decode($selectedTimeCardIDs, true);

            if ($selectedTimeCardIDs != null && count($selectedTimeCardIDs) > 0){
                //build base query
                $responseArray = new Query;
                $responseArray  ->select('*')
                    ->from(["fnGenerateOasisTimeCardByTimeCardID(:TimeCardID)"])
                    ->addParams([':TimeCardID' => $selectedTimeCardIDs]);

                $responseArray = $responseArray->createCommand(BaseActiveRecord::getDb())->query();

            } else {
                //rbac permission check
                if (PermissionsController::can('timeCardGetAllCards')) {
                    //check if week is prior or current to determine appropriate view
                    if ($week == 'prior') {
                        $responseArray = TimeCardSumHoursWorkedPriorWeekWithProjectName::find()->orderBy('UserID,TimeCardStartDate,ProjectID')->createCommand();//->all();
                        $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                        //$timeCardArray = array_map(function ($model) {return $model->attributes;},$timeCardsArr);
                    } elseif ($week == 'current') {
                        $responseArray = TimeCardSumHoursWorkedCurrentWeekWithProjectName::find()->orderBy('UserID,TimeCardStartDate,ProjectID')->createCommand();//->all();
                        $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                        //$timeCardArray = array_map(function ($model) {return $model->attributes;},$timeCards);
                    }
                } //rbac permission check
                elseif (PermissionsController::can('timeCardGetOwnCards')) {
                    $userID = self::getUserFromToken()->UserID;
                    //get user project relations array
                    $projects = ProjectUser::find()
                        ->where("ProjUserUserID = $userID")
                        ->all();
                    $projectsSize = count($projects);

                    //check if week is prior or current to determine appropriate view
                    if ($week == 'prior' && $projectsSize > 0) {
                        $timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectName::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);

                        for ($i = 0; $i < $projectsSize; $i++) {
                            $projectID = $projects[$i]->ProjUserProjectID;
                            $timeCards->andWhere(['ProjectID' => $projectID]);
                        }
                        $responseArray = $timeCards->orderBy('UserID,TimeCardStartDate,ProjectID')->createCommand();//->all();
                        $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time

                    } elseif ($week == 'current' && $projectsSize > 0) {
                        $timeCards = TimeCardSumHoursWorkedCurrentWeekWithProjectName::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
                        for ($i = 0; $i < $projectsSize; $i++) {
                            $projectID = $projects[$i]->ProjUserProjectID;
                            $timeCards->andWhere(['ProjectID' => $projectID]);
                        }
                        $responseArray = $timeCards->orderBy('UserID,TimeCardStartDate,ProjectID')->createCommand();//->all();
                        $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                    }
                } else {
                    throw new ForbiddenHttpException;
                }
            }

            if (!empty($responseArray))
            {
                $this->processAndOutputCsvResponse($responseArray);
                return '';
            }
            $this->setCsvHeaders();
            //send response
            return '';
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetPayrollData($selectedTimeCardIDs = [])
    {
        // RBAC permission check is embedded in this action
        try{
            //set db target headers
            TimeCardSumHoursWorkedCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;
            
            $selectedTimeCardIDs = json_decode($selectedTimeCardIDs, true);

            if ($selectedTimeCardIDs != null && count($selectedTimeCardIDs) > 0){
                //build base query
                $responseArray = new Query;
                $responseArray  ->select('*')
                    ->from(["fnGenerateQBDummyPayrollTimeCardID(:TimeCardID)"])
                    ->addParams([':TimeCardID' => $selectedTimeCardIDs]);

                $responseArray = $responseArray->createCommand(BaseActiveRecord::getDb())->query();

            }

            if (!empty($responseArray))
            {
                $this->processAndOutputCsvResponse($responseArray);
                return '';
            }
            $this->setCsvHeaders();
            //send response
            return '';
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    // helper method for setting the csv header for tracker maps csv output
    public function setCsvHeaders(){
        header('Content-Type: text/csv;charset=UTF-8');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    // helper method for outputting csv data without storing the whole result
    public function processAndOutputCsvResponse($reader){
        Yii::$app->response->format = Response::FORMAT_RAW;

        $this->setCsvHeaders();
        // TODO find a way to use Yii response but without storing the whole response content in a variable
        $firstLine = true;
        $fp = fopen('php://output','w');

        while($row = $reader->read()){

            if($firstLine) {
                $firstLine = false;
                fwrite($fp, implode(',', array_keys($row)) . "\r\n");
            }
            fwrite($fp, implode(',', $row) . "\r\n");
        }
        fclose($fp);
    }
}
