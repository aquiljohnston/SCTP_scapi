<?php

namespace app\modules\v2\controllers;

use app\modules\v2\models\ChartOfAccountType;
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
			$headers 	= getallheaders();
			//set db target
			AllTimeCardsCurrentWeek::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeCardGetCard');
			
			//get project based on header
			$project 	= Project::find()
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

    public function actionGetCards($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $projectName = null)
    {
        $weekParameterIsInvalidString = "The acceptable values for week are 'prior' and 'current'";
        // RBAC permission check is embedded in this action
        try
        {
            //get headers
            $headers 			= getallheaders();
            //get client header
            $client 			= $headers['X-Client'];

            //url decode filter value
            $filter 			= urldecode($filter);

            //set db target headers
            $headers 			= getallheaders();
            TimeCardSumHoursWorkedCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response 			= Yii::$app->response;
            $response-> format 	= Response::FORMAT_JSON;

            //response array of time cards
            $timeCardsArr 		= [];
            $responseArray 		= [];

            //build base query
            $timeCards = new Query;
            $timeCards->select('*')
                ->from(["fnTimeCardByDate(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate]);

            $records = $timeCards->all(BaseActiveRecord::getDb());    


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
                    ['like', 'UserFullName', $filter],
                    ['like', 'Project', $filter],
                    ['like', 'TimeCardApprovedFlag', $filter]
                    // TODO: Add TimeCardTechID -> name and username to DB view and add to filtered fields
                ]);
            }

            if($projectName!= null && isset($timeCards)) {
                $timeCards->andFilterWhere([
                    'or',
                    //['like', 'UserName', $filter],
                    ['like', 'UserFullName', $filter],
                    ['like', 'Project', $filter],
                    ['like', 'ProjectName', $projectName],
                    ['like', 'TimeCardApprovedFlag', $filter]
                ]);
            }

            //iterate and stash project name
            $allTheProjects = [];
            foreach ($records as $p) {
     			$allTheProjects[$p['ProjectName']] = $p['ProjectName'];
            }
            //remove dupes
            $allTheProjects = array_unique($allTheProjects);
            //abc order for all
            asort($allTheProjects);
          
            $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
            $timeCardsArr = $paginationResponse['Query']->orderBy('UserID,TimeCardStartDate,TimeCardProjectID')->all(BaseActiveRecord::getDb());
            $responseArray['assets'] = $timeCardsArr;
            $responseArray['pages'] = $paginationResponse['pages'];
            $responseArray['projectDropDown'] = $allTheProjects;

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
                BaseActiveController::processAndOutputCsvResponse($responseArray);
                return '';
            }
            BaseActiveController::setCsvHeaders();
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
                BaseActiveController::processAndOutputCsvResponse($responseArray);
                return '';
            }
            BaseActiveController::setCsvHeaders();
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

    /**
     * Create New Task Entry in CT DB
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreateTaskEntry()
    {
        $successFlag = 0;
        try {
            //set db target
            TimeCard::setClient(BaseActiveController::urlPrefix());

            //get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);

            // set up db connection
            $connection = BaseActiveRecord::getDb();
            $processJSONCommand = $connection->createCommand("EXECUTE spAddActivityAndTime :TimeCardID, :TaskName , :Date, :StartTime, :EndTime, :CreatedByUserName, :ChargeOfAccountType");
            $processJSONCommand->bindParam(':TimeCardID', $data['TimeCardID'], \PDO::PARAM_STR);
            $processJSONCommand->bindParam(':TaskName', $data['TaskName'], \PDO::PARAM_STR);
            $processJSONCommand->bindParam(':Date', $data['Date'], \PDO::PARAM_STR);
            $processJSONCommand->bindParam(':StartTime', $data['StartTime'], \PDO::PARAM_STR);
            $processJSONCommand->bindParam(':EndTime', $data['EndTime'], \PDO::PARAM_STR);
            $processJSONCommand->bindParam(':CreatedByUserName', $data['CreatedByUserName'], \PDO::PARAM_STR);
            $processJSONCommand->bindParam(':ChargeOfAccountType', $data['ChargeOfAccountType'], \PDO::PARAM_STR);
            $processJSONCommand->execute();
            $successFlag = 1;

        } catch (\Exception $e) {
            BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], [
                'TimeCardID' => $data['TimeCardID'],
                'TaskName' => $data['TaskName'],
                'Date' => $data['Date'],
                'StartTime' => $data['StartTime'],
                'EndTime' => $data['EndTime'],
                'CreatedByUserName' => $data['CreatedByUserName'],
                'ChargeOfAccountType' => $data['ChargeOfAccountType'],
                'SuccessFlag' => $successFlag
            ]);
        }

        //build response format
        $dataArray =  [
            'TimeCardID' => $data['TimeCardID'],
            'SuccessFlag' => $successFlag
        ];
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $dataArray;
    }

    /**
     * Get ChargeOfAccountType From CT DB
     * @return mixed
     */
    public function actionGetChargeOfAccountType(){
        //set db target
        ChartOfAccountType::setClient(BaseActiveController::urlPrefix());

        $chartOfAccountType = ChartOfAccountType::find()
            ->all();

        //load data into array
        /*$dataArray = [];
        $dataArray['assets'] = $chartOfAccountType;

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $dataArray;*/

        $namePairs = [];
        $codesSize = count($chartOfAccountType);

        for($i=0; $i < $codesSize; $i++)
        {
            $namePairs[$chartOfAccountType[$i]->ChartOfAccountID]= $chartOfAccountType[$i]->ChartOfAccountDescription;
        }


        $response = Yii::$app ->response;
        $response -> format = Response::FORMAT_JSON;
        $response -> data = $namePairs;

        return $response;
    }
}
