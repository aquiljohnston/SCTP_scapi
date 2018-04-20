<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\constants\Constants;
use app\modules\v2\models\TimeCard;
use app\modules\v2\models\TimeEntry;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\Project;
use app\modules\v2\models\ProjectUser;
use app\modules\v2\models\AllTimeCardsCurrentWeek;
use app\modules\v2\models\TimeCardSumHoursWorkedCurrentWeekWithProjectName;
use app\modules\v2\models\TimeCardSumHoursWorkedPriorWeekWithProjectName;
use app\modules\v2\models\TimeCardEventHistory;
use app\modules\v2\models\AccountantSubmit;
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
					'get-time-cards-history-data' => ['get'],
					'get-payroll-data' => ['get'],
					'show-entries' => ['get'],
					'get-accountant-view' => ['get'],
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
		//may want to move this into show entries because I believe that is the only location it is called from
		try
		{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeCardView');
			
			$timeCard = TimeCard::find()
				->select([
					'TimeCardID',
					'TimeCardProjectID',
					'TimeCardApprovedBy',
					'TimeCardApprovedFlag',
					'ProjectName',
					'UserFirstName',
					'UserLastName'])
				->innerJoin('ProjectTb', 'ProjectTb.ProjectID = TimeCardTb.TimeCardProjectID')
				->innerJoin('UserTb', 'UserTb.UserID = TimeCardTb.TimeCardTechID')
				->where(['TimeCardID' => $id])
				->asArray()
				->one();
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
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Time Card Approve', $approvedBy, BaseActiveController::urlPrefix());
			
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
					$card->TimeCardApprovedFlag = 1;
					$card->TimeCardApprovedBy = $approvedBy;
					$card->update();
					//log approvals
					self::logTimeCardHistory(Constants::TIME_CARD_APPROVAL, $card->TimeCardID);
				}
				$transaction->commit();
				//log approval of cards
				$response->setStatusCode(200);
				$response->data = $approvedCards;
				return $response;
			} catch (ForbiddenHttpException $e) {
				throw new ForbiddenHttpException;
			}
			catch(\Exception $e) //if transaction fails rollback changes and send error
			{
				$transaction->rollBack();
				//archive error
				BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				return $response;
				
			}
		}
		catch(\Exception $e)  
		{
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
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

    public function actionGetCards($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $projectID = null)
    {
        // RBAC permission check is embedded in this action
        try
        {
            //get headers
            $headers 			= getallheaders();
            //get client header
            $client 			= $headers['X-Client'];

            //url decode filter value
            $filter 			= urldecode($filter);
			//explode by delimiter to allow for multi search
			$delimiter = ',';
			$filterArray = explode($delimiter, $filter);

            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //format response
            $response 			= Yii::$app->response;
            $response-> format 	= Response::FORMAT_JSON;

            //response array of time cards
            $timeCardsArr 		= [];
            $responseArray 		= [];
			$projectAllOption = [];
			$allTheProjects = [];
			$showProjectDropDown = false;

            //build base query
            $timeCards = new Query;
            $timeCards->select('*')
                ->from(["fnTimeCardByDate(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate]); 

            //if is scct website get all or own
            if(BaseActiveController::isSCCT($client))
            {
				$showProjectDropDown = true;
				/*
                 * Check if user can get their own cards
                 */
                if (!PermissionsController::can('timeCardGetAllCards') && PermissionsController::can('timeCardGetOwnCards'))
                {
                    $userID = self::getUserFromToken()->UserID;
                    //get user project relations array
                    $projects = ProjectUser::find()
                        ->where("ProjUserUserID = $userID")
                        ->all();
                    $projectsSize = count($projects);
                    if($projectsSize > 0)
                    {
                        $timeCards->where(['TimeCardProjectID' => $projects[0]->ProjUserProjectID]);
                    }
					else
					{
						//can only get own but has no project relations
						throw new ForbiddenHttpException;
					}
                    if($projectsSize > 1)
                    {
						//add all option to project dropdown if there will be more than one option
						$projectAllOption = [""=>"All"];
                        for($i=1; $i < $projectsSize; $i++)
                        {
                            $relatedProjectID = $projects[$i]->ProjUserProjectID;
                            $timeCards->orWhere(['TimeCardProjectID'=>$relatedProjectID]);
                        }
                    }
                }
				/*
                 * Check if user can get all cards
                 */
                elseif (PermissionsController::can('timeCardGetAllCards'))
                {
					$projectAllOption = [""=>"All"];
                }
				else
				{
					//no permissions to get cards
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
                $projectsSize = count($project);
            }

			//get records post user/permissions filter for project dropdown(timing for this execution is very important)
			$dropdownRecords = $timeCards->all(BaseActiveRecord::getDb());
			
            if($filterArray!= null && isset($timeCards)) { //Empty strings or nulls will result in false
				//initialize array for filter query values
				$filterQueryArray = array('or');
				//loop for multi search
				for($i = 0; $i < count($filterArray); $i++)
				{
					//remove leading space from filter string
					$trimmedFilter = trim($filterArray[$i]);
					array_push($filterQueryArray,
						['like', 'UserFullName', $trimmedFilter],
						['like', 'ProjectName', $trimmedFilter]
					);
				}
				$timeCards->andFilterWhere($filterQueryArray);
            }

            if($projectID!= null && isset($timeCards)) {
                $timeCards->andFilterWhere([
                    'and',
                    ['TimeCardProjectID' => $projectID],
                ]);
            }

			//get project list for dropdown based on time cards available
			$allTheProjects = self::extractProjectsFromTimeCards($dropdownRecords, $projectAllOption);
		  
            $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
            $timeCardsArr = $paginationResponse['Query']->orderBy('UserID,TimeCardStartDate,TimeCardProjectID')->all(BaseActiveRecord::getDb());
            // check if approved time card exist in the data
            $approvedTimeCardExist = $this->CheckApprovedTimeCardExist($timeCardsArr);
            $projectWasSubmitted   = $this->CheckAllAssetsSubmitted($timeCardsArr);
            $responseArray['approvedTimeCardExist'] = $approvedTimeCardExist;
            $responseArray['assets'] 				= $timeCardsArr;
            $responseArray['pages'] 				= $paginationResponse['pages'];
            $responseArray['projectDropDown'] 		= $allTheProjects;
            $responseArray['showProjectDropDown'] 	= $showProjectDropDown;
            $responseArray['projectSubmitted'] 		= $projectWasSubmitted;

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
	
	public function actionGetAccountantView($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $projectID = null)
	{
		try{
			//url decode filter value
            $filter = urldecode($filter);
			//explode by delimiter to allow for multi search
			$delimiter = ',';
			$filterArray = explode($delimiter, $filter);
			
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			
			//response array of time cards
            $timeCards = [];
            $responseArray = [];
			$allTheProjects = [""=>"All"];
			$showProjectDropDown = true;
			//used to get current week if date range falls in the middle of the week
			$sevenDaysPriorToEnd = date('m/d/Y', strtotime($endDate . ' -7 days'));
			
			//build base query
            $cardQuery = AccountantSubmit::find()
				->where(['between', 'StartDate', $startDate, $endDate])
                ->orWhere(['between', 'EndDate', $startDate, $endDate])                
                ->orWhere(['between', 'StartDate', $sevenDaysPriorToEnd, $endDate]);              
			
			//get records for project dropdown(timing for this execution is very important)
			$dropdownRecords = $cardQuery->all(BaseActiveRecord::getDb());
			
			//add project filter
			if($projectID!= null) 
			{
                $cardQuery->andFilterWhere([
                    'and',
                    ['ProjectID' => $projectID],
                ]);
            }
			
			//add search filter
			if($filter != null) 
			{
                $cardQuery->andFilterWhere([
                    'or',
                    ['like', 'ProjectName', $filter],
                    ['like', 'ProjectManager', $filter],
                    ['like', 'ApprovedBy', $filter],
                ]);
            }
			
			//get project list for dropdown based on time cards available
			$allTheProjects = self::extractProjectsFromTimeCards($dropdownRecords, $allTheProjects);
			
			//paginate
			$paginationResponse = self::paginationProcessor($cardQuery, $page, $listPerPage);
            $timeCards = $paginationResponse['Query']->orderBy('ProjectName, StartDate')->all(BaseActiveRecord::getDb());
			
			//copying this functionality from get cards route, want to look into a way to integrate this with the regular submit check
			//this check seems to have some issue and is only currently being applied to the post filter data set.
			$projectWasSubmitted   = $this->CheckAllAssetsSubmitted($timeCards);

            $responseArray['assets'] = $timeCards;
            $responseArray['pages'] = $paginationResponse['pages'];
            $responseArray['projectDropDown'] = $allTheProjects;
            $responseArray['showProjectDropDown'] = $showProjectDropDown;
            $responseArray['projectSubmitted'] = $projectWasSubmitted;

			$response->data = $responseArray;
			return $response;
		}
		catch(ForbiddenHttpException $e) {
			throw $e;
		}
		catch(\Exception $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionGetAccountantDetails($projectID, $startDate, $endDate)
	{
		try
		{
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			$detailsQuery = new Query;
            $timeCards = $detailsQuery->select('*')
                ->from(["fnTimeCardByDate(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate])
				->where(['TimeCardProjectID' => $projectID])
				->all(BaseActiveRecord::getDb());
				
			//format response
			$responseArray['details'] = $timeCards;
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
		}
		catch(ForbiddenHttpException $e) {
			throw $e;
		}
		catch(\Exception $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}

    public function actionGetTimeCardsHistoryData($projectName,$timeCardName,$week = null,$weekStart=null,$weekEnd=null, $write=false,$type=null, array $data=null)
    {
        // RBAC permission check is embedded in this action
        try{
            //set db target headers
            TimeCardSumHoursWorkedCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;
            $responseArray = [];

            //response array of time cards
            $timeCardsArr 					= [];
            //$selectedTimeCardIDs = json_decode($selectedTimeCardIDs, true);

            $arrayProjectName 				= $projectName;
            $writeTimeCardFile				= false;
            $fileResponse 					= [];
            $fileResponse['was_written'] 	=  false;
            $fileResponse['type']			=  'Exception'; 

            Yii::trace("JSONESSEX-TIMECARDHIS");            
           // Yii::trace("JSONESSEX-TC ".$arrayProjectName);            
            Yii::trace("JSONESSEX-WS ".$weekStart);
            Yii::trace("JSONESSEX-WE ".$weekEnd);
            Yii::trace("JSONESSEX-CN ".$timeCardName);

            if(!$write){

            	try{
       			$responseArray = BaseActiveRecord::getDb();
				$getEventsCommand = $responseArray->createCommand("SET NOCOUNT ON EXECUTE spGenerateOasisTimeCardByProject :projectName, :weekStart,:weekEnd");
				$getEventsCommand->bindParam(':projectName',$projectName,  \PDO::PARAM_STR);
				$getEventsCommand->bindParam(':weekStart', $weekStart,  \PDO::PARAM_STR);
				$getEventsCommand->bindParam(':weekEnd', $weekEnd,  \PDO::PARAM_STR);
				$responseArray = $getEventsCommand->query();  
       			} catch(\Exception $e) {
    	        Yii::trace('PDO EXCEPTION'.$e->getMessage());
        	    throw new \yii\web\HttpException(400);
 	      		 }


				if($responseArray->count() !=0){

					$writeTimeCardFile = true;

				}
            }
            //WRAP DB IN ITS OWN TRY CATCH SINCE IT IS A GENERAL "CATCH-ALL" EXCEPTION TYPE
       		
 	       error_log(print_r($data,true));
 	

				if($writeTimeCardFile || $write){
                //rbac permission check
                if (PermissionsController::can('timeCardGetAllCards')) {
                    //check if week is prior or current to determine appropriate view
                    if ($week == 'prior') {
                        $responseArray = TimeCardSumHoursWorkedPriorWeekWithProjectName::find()->orderBy('UserID,TimeCardStartDate,ProjectID')->createCommand();//->all();
                       // $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                        //$timeCardArray = array_map(function ($model) {return $model->attributes;},$timeCardsArr);
                    } elseif ($week == 'current') {
                        $responseArray = TimeCardSumHoursWorkedCurrentWeekWithProjectName::find()->orderBy('UserID,TimeCardStartDate,ProjectID')->createCommand();//->all();
                        //$responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
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
                       // $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time

                    } elseif ($week == 'current' && $projectsSize > 0) {
                        $timeCards = TimeCardSumHoursWorkedCurrentWeekWithProjectName::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
                        for ($i = 0; $i < $projectsSize; $i++) {
                            $projectID = $projects[$i]->ProjUserProjectID;
                            $timeCards->andWhere(['ProjectID' => $projectID]);
                        }
                        $responseArray = $timeCards->orderBy('UserID,TimeCardStartDate,ProjectID')->createCommand();//->all();
                        //$responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                    }
                } else {
                    throw new ForbiddenHttpException;
                }
                if($write){
                	try {	
                		BaseActiveController::processAndWriteCsv($data,$timeCardName,$type);
                		$fileResponse['was_written'] = 'file_written';
                	 	$fileResponse['type']		 = 'Success'; 	
              			$response -> data 			 = $fileResponse;
               		}catch(\Exception $e) {
          			 	Yii::trace('Exception '.$e->getMessage());
          				throw new \yii\web\HttpException(800);
      				}  
             
                } else {
        	 			$fileResponse['was_written'] 	=  'ready_to_write';
         				$fileResponse['type']			=  'Success';	
         				$fileResponse['data']			=  $responseArray;	
         				$response -> data = $fileResponse;
            	 	}
            }else{
            	$fileResponse['was_written'] 	=  'nothing_to_write';
              	$fileResponse['type']			=  'Success'; 	
              	$fileResponse['data']			=  'nothing_to_write'; 	
                $response -> data 				=  $fileResponse;
            }
			//log submission
			self::logTimeCardHistory(Constants::TIME_CARD_SUBMISSION_OASIS, null, $weekStart, $weekEnd);

        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
       } catch(\Exception $e) {
           Yii::trace('Exception '.$e->getMessage());
          throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetPayrollData($cardName,$projectName,$weekStart=null,$weekEnd=null,$write=false,$type=null,array $data=null)
    {

        // RBAC permission check is embedded in this action
        try{
            //set db target headers
            TimeCardSumHoursWorkedCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;
            $responseArray = [];


            $writePayrollFile				= false;
            $fileResponse 					= [];
            $fileResponse['was_written'] 	=  FALSE;
            $fileResponse['type']		=  'Exception'; 	

            Yii::trace("JSONESSEX-PAYCARDHIS");  
            //Yii::trace("JSONESSEX-PR ".$arrayProjectName);
            Yii::trace("JSONESSEX-WS ".$weekStart);
            Yii::trace("JSONESSEX-WE ".$weekEnd);
            Yii::trace("JSONESSEX-CN ".$cardName);
    		
    		//
 			try{
	                $responseArray = BaseActiveRecord::getDb();
					$getEventsCommand = $responseArray->createCommand("SET NOCOUNT ON EXECUTE spGenerateQBDummyPayrollByProject :projectName, :weekStart,:weekEnd");
					$getEventsCommand->bindParam(':projectName', $projectName,  \PDO::PARAM_STR);
					$getEventsCommand->bindParam(':weekStart', $weekStart,  \PDO::PARAM_STR);
					$getEventsCommand->bindParam(':weekEnd', $weekEnd,  \PDO::PARAM_STR);
					$responseArray = $getEventsCommand->query(); 
				} catch(\Exception $e) {
	            	Yii::trace('PDO EXCEPTION '.$e->getMessage());
	            	throw new \yii\web\HttpException(400);
        	}

				if($responseArray->count() !=0){
					$writePayrollFile	= true;
				} 

					if($writePayrollFile) {
					 	 if($write){
                			try {	
		                		BaseActiveController::processAndWriteCsv($data,$cardName,$type);
		                		$fileResponse['was_written'] = 'file_written';
		                	 	$fileResponse['type']		 = 'Success'; 	
		              			$response -> data 			 = $fileResponse;
               				}catch(\Exception $e) {
          			 			Yii::trace('Exception '.$e->getMessage());
          						throw new \yii\web\HttpException(800);
      						}  
             
              	 		 } else {
	        	 			$fileResponse['was_written'] 	=  'ready_to_write';
	         				$fileResponse['type']			=  'Success';	
	         				$fileResponse['data']			=  $responseArray;	
	         				$response -> data = $fileResponse;
            	 		}

          	  } else {
          	  	$fileResponse['was_written'] 	=  'nothing_to_write';
             	$fileResponse['type']			=  'Success';
             	$fileResponse['data']			=  'nothing_to_write';  	
             	$response -> data = $fileResponse;
          	  }
			//log submission
			self::logTimeCardHistory(Constants::TIME_CARD_SUBMISSION_QB, null, $weekStart, $weekEnd);
      	
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
       }
    }

     
	public function actionGetAdpData($adpFileName,$projectName,$weekStart=null,$weekEnd=null,$write=false,$type=null,array $data=null)
    {

        // RBAC permission check is embedded in this action
        try{
            //set db target headers
            TimeCardSumHoursWorkedCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;

            $writeADPFile	= false;
            $responseArray = [];
            $fileResponse 					= [];
            $fileResponse['was_written'] 	=  FALSE;
            $fileResponse['message']		=  'Empty ADP File'; 	

            Yii::trace("JSONESSEX-ADPHIS");  
            //Yii::trace("JSONESSEX-PR ".$arrayProjectName);
            Yii::trace("JSONESSEX-WS ".$weekStart);
            Yii::trace("JSONESSEX-WE ".$weekEnd);
            Yii::trace("JSONESSEX-CN ".$adpFileName);
            
   
 			try{
                $responseArray = BaseActiveRecord::getDb();
				$getEventsCommand = $responseArray->createCommand("SET NOCOUNT ON EXECUTE spGenerateADPTimeCardByProject :projectName, :weekStart,:weekEnd");
				$getEventsCommand->bindParam(':projectName', $projectName,  \PDO::PARAM_STR);
				$getEventsCommand->bindParam(':weekStart', $weekStart,  \PDO::PARAM_STR);
				$getEventsCommand->bindParam(':weekEnd', $weekEnd,  \PDO::PARAM_STR);
				$responseArray = $getEventsCommand->query();
				} catch(\Exception $e) {
	            	Yii::trace('PDO EXCEPTION '.$e->getMessage());
	            	throw new \yii\web\HttpException(400);
        	}

				if($responseArray->count() !=0){

					$writeADPFile	= true;

				} 

				if($writeADPFile) {
					 if($write){
                			try {	
		                		BaseActiveController::processAndWriteCsv($data,$adpFileName,$type);
		                		$fileResponse['was_written'] = 'file_written';
		                	 	$fileResponse['type']		 = 'Success'; 	
		              			$response -> data 			 = $fileResponse;
               				}catch(\Exception $e) {
          			 			Yii::trace('Exception '.$e->getMessage());
          						throw new \yii\web\HttpException(800);
      						}  
             
              	 		 } else {
	        	 			$fileResponse['was_written'] 	=  'ready_to_write';
	         				$fileResponse['type']			=  'Success';	
	         				$fileResponse['data']			=  $responseArray;	
	         				$response -> data = $fileResponse;
            	 		}
          	  } else {
          	  	$fileResponse['was_written'] 		=  'nothing_to_write';
             	$fileResponse['type']				=  'Success'; 
             	$fileResponse['data']				=  'nothing_to_write'; 	
             	$response -> data = $fileResponse;
          	  }
			//log submission
			self::logTimeCardHistory(Constants::TIME_CARD_SUBMISSION_ADP, null, $weekStart, $weekEnd);
      	
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
       }
    }

    public function actionResetCometTrackerProcess($projectName,$weekStart=null,$weekEnd=null,$process)
    {

        // RBAC permission check is embedded in this action
        try{
            //set db target headers
          	BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //format response
            $response 			= Yii::$app->response;
            $response-> format 	= Response::FORMAT_JSON;

            $responseArray 		= [];


            Yii::trace("RESETPROCESSCALLED");  

                $responseArray = BaseActiveRecord::getDb();
				$getEventsCommand = $responseArray->createCommand("SET NOCOUNT ON EXECUTE spResetSubmitFlag :projectName,:weekStart,:weekEnd,:process");
				$getEventsCommand->bindParam(':projectName', $projectName,  \PDO::PARAM_STR);
				$getEventsCommand->bindParam(':weekStart', $weekStart,  \PDO::PARAM_STR);
				$getEventsCommand->bindParam(':weekEnd', $weekEnd,  \PDO::PARAM_STR);
				$getEventsCommand->bindParam(':process', $process,  \PDO::PARAM_STR);
				$responseArray 	  	= $getEventsCommand->query();  

				$status['success']	= true;	
             	$response -> data 	= $status;	
			
			//log submission
			self::logTimeCardHistory(Constants::TIME_CARD_SUBMISSION_RESET, null, $weekStart, $weekEnd);
			
        } catch(\Exception $e) {
            Yii::trace('PDO EXCEPTION: '.$e->getMessage());
            throw new \yii\web\HttpException(400);
       }
    }

    /**
     * Check if there is at least one time card has been approved
     * @param $timeCardsArr
     * @return boolean
     */
    private function CheckApprovedTimeCardExist($timeCardsArr){
        $approvedTimeCardExist = false;
        foreach ($timeCardsArr as $item){
            if ($item['TimeCardApprovedFlag'] == 1){
                $approvedTimeCardExist = true;
                break;
            }
        }
        return $approvedTimeCardExist;
    }



     /**
     * Check if project was submitted to Oasis and QB
     * @param $timeCardsArr
     * @return boolean
     */
    private function CheckAllAssetsSubmitted($timeCardsArr){
        $allAssetsCount = count($timeCardsArr);
        $submittedCount = 0;
        $allSubmitted   = FALSE;
		
        foreach ($timeCardsArr as $item)
		{
			$oasisKey = array_key_exists('TimeCardOasisSubmitted', $item) ? 'TimeCardOasisSubmitted' : 'OasisSubmitted';
			$qbKey = array_key_exists('TimeCardQBSubmitted', $item) ? 'TimeCardQBSubmitted' : 'QBSubmitted';
			
            if ($item[$oasisKey] == "Yes" && $item[$qbKey] == "Yes" ){
                $submittedCount++;
            }
        }

        if ($allAssetsCount == $submittedCount){
        	$allSubmitted = TRUE;
        }

        return $allSubmitted;
         
    }

    /**
     * Check if submit button should be enabled/disabled by calling DB fnSubmit function
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionCheckSubmitButtonStatus(){
        try{
			//get headers
            $headers = getallheaders();
            //get client header
            $client = $headers['X-Client'];
			
            //set db target
            TimeCard::setClient(BaseActiveController::urlPrefix());

            //get body data
            $data = file_get_contents("php://input");
			$submitCheckData = json_decode($data, true)['submitCheck'];
			
			//if is not scct project name will always be the current client
			if(BaseActiveController::isSCCT($client))
			{
				$projectName  = $submitCheckData['ProjectName'];
			}else{
				$project = Project::find()
					->where(['ProjectUrlPrefix' => $client])
					->one();
				$projectName = array($project->ProjectID);
			}

            //build base query
			$responseArray = new Query;
            $responseArray->select('*')
                ->from(["fnSubmitAccountant(:StartDate , :EndDate)"])
                ->addParams([
					//':ProjectName' => json_encode($projectName), 
					':StartDate' => $submitCheckData['StartDate'], 
					':EndDate' => $submitCheckData['EndDate']
					]);
            $submitButtonStatus = $responseArray->one(BaseActiveRecord::getDb());
            $responseArray = $submitButtonStatus;

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $responseArray;

            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }
	
	private function extractProjectsFromTimeCards($dropdownRecords, $projectAllOption)
	{
		//iterate and stash project name $p['TimeCardProjectID']
		foreach ($dropdownRecords as $p) {
			//currently only two option exist for key would have to update this if more views/tables/functions use this function
			$key = array_key_exists('TimeCardProjectID', $p) ? $p['TimeCardProjectID'] : $p['ProjectID'];
			$value = $p['ProjectName'];
			$allTheProjects[$key] = $value;
		}
		//remove dupes
		$allTheProjects = array_unique($allTheProjects);
		//abc order for all
		asort($allTheProjects);
		//appened all option to the front
		$allTheProjects = $projectAllOption + $allTheProjects;
		
		return $allTheProjects;
	}
	
	private function logTimeCardHistory($type, $timeCardID = null, $startDate = null, $endDate = null)
	{
		try
		{
			//create and populate model
			$historyRecord = new TimeCardEventHistory;
			$historyRecord->Date = BaseActiveController::getDate();
			$historyRecord->Name = self::getUserFromToken()->UserName;
			$historyRecord->Type = $type;
			$historyRecord->TimeCardID = $timeCardID;
			$historyRecord->StartDate = $startDate;
			$historyRecord->EndDate = $endDate;
			
			//save
			if(!$historyRecord->save())
			{
				//throw error on failure
				throw BaseActiveController::modelValidationException($newInspection);
			}
		}
		catch(\Exception $e)
		{
			//catch and log errors
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
		}
	}
}
