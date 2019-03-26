<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\constants\Constants;
use app\modules\v3\models\TimeCard;
use app\modules\v3\models\Project;
use app\modules\v3\models\ProjectUser;
use app\modules\v3\models\AllTimeCardsCurrentWeek;
use app\modules\v3\models\TimeCardEventHistory;
use app\modules\v3\models\AccountantSubmit;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\authentication\TokenAuth;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\data\Pagination;
use yii\db\Query;

/**
 * TimeCardController implements the CRUD actions for TimeCard model.
 */
class TimeCardController extends BaseActiveController
{
	public $modelClass = 'app\modules\v3\models\TimeCard';

	
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
					'approve-cards'  => ['put'],
					'show-entries' => ['get'],
					'get-cards' => ['get'],
					'get-accountant-view' => ['get'],
					'get-accountant-details' => ['get'],
					'p-m-submit' => ['put'],
					'accountant-submit' => ['put'],
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
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
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

			$dataArray = [];

			$entriesQuery = new Query;
			$entriesQuery->select('*')
				->from("fnTimeEntrysByTimeCard(:cardID)")
				->addParams([':cardID' => $cardID]);
			$entries = $entriesQuery->all(BaseActiveRecord::getDb());

			$cardQuery = new Query;
			$cardQuery->select('*')
				->from("fnTimeCardByID(:cardID)")
				->addParams([':cardID' => $cardID]);
			$card = $cardQuery->one(BaseActiveRecord::getDb());

			$dataArray['show-entries'] = $entries;
			$dataArray['card'] = $card;

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $dataArray;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}

    public function actionGetCards($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $projectID = null,
		$sortField = 'UserFullName', $sortOrder = 'ASC', $employeeID = null)
    {
        // RBAC permission check is embedded in this action
        try{
            //get headers
            $headers = getallheaders();
            //get client header
            $client = $headers['X-Client'];

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

            //response array of time cards//
            $timeCardsArr = [];
            $responseArray = [];
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
            }

			//get records post user/permissions filter for project dropdown(timing for this execution is very important)
			$projectDropdownRecords = $timeCards->all(BaseActiveRecord::getDb());

			//apply project filter
            if($projectID!= null && isset($timeCards)) {
                $timeCards->andFilterWhere([
                    'and',
                    ['TimeCardProjectID' => $projectID],
                ]);
            }

			//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
			$employeeDropdownRecords = $timeCards->all(BaseActiveRecord::getDb());
			
			//apply employee filter
			if($employeeID!= null && isset($timeCards)) {
                $timeCards->andFilterWhere([
                    'and',
                    ['UserID' => $employeeID],
                ]);
            }
			
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
			
			//get project list for dropdown based on time cards available
			$projectDropDown = self::extractProjectsFromTimeCards($projectDropdownRecords, $projectAllOption);
			
			//get employee list for dropdown based on time cards available
			$employeeDropDown = self::extractEmployeesFromTimeCards($employeeDropdownRecords);

            $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
            $timeCardsArr = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());
            //check if approved time card exist in the data
            $unapprovedTimeCardExist = $this->CheckUnapprovedTimeCardExist($timeCardsArr);
            $projectWasSubmitted   = $this->CheckAllAssetsSubmitted($timeCardsArr);
            
            $responseArray['assets'] 				= $timeCardsArr;
            $responseArray['pages'] 				= $paginationResponse['pages'];
            $responseArray['projectDropDown'] 		= $projectDropDown;
            $responseArray['employeeDropDown'] 		= $employeeDropDown;
            $responseArray['showProjectDropDown'] 	= $showProjectDropDown;
			$responseArray['unapprovedTimeCardExist'] = $unapprovedTimeCardExist;
            $responseArray['projectSubmitted'] 		= $projectWasSubmitted;
			$response->data = $responseArray;
			$response->setStatusCode(200);
			return $response;
        }catch(ForbiddenHttpException $e) {
			throw $e;
		}catch(\Exception $e){
		   throw new \yii\web\HttpException(400);
		}
    }

	public function actionGetAccountantView($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $projectID = null,
		$sortField = 'ProjectName', $sortOrder = 'ASC')
	{
		try{
			//url decode filter value
            $filter = urldecode($filter);
			//explode by delimiter to allow for multi search
			$delimiter = ',';
			$filterArray = explode($delimiter, $filter);

			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
			PermissionsController::requirePermission('timeCardGetAccountantView');

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
            $timeCards = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());

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
		} catch(ForbiddenHttpException $e) {
			throw $e;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}

	public function actionGetAccountantDetails($projectID, $startDate, $endDate)
	{
		try
		{
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
			PermissionsController::requirePermission('timeCardGetAccountantDetails');

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
		} catch(ForbiddenHttpException $e) {
			throw $e;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionPMSubmit()
	{
		try
		{
			//set db target
			TimeCard::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('timeCardPmSubmit');
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get userid
			$approvedBy = self::getUserFromToken()->UserName;
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Time Card Submittal', $approvedBy, BaseActiveController::urlPrefix());
			
			//parse json	
			$cardIDs = $data["projectIDArray"];
			$connection = BaseActiveRecord::getDb();
			// get all timecards
			$max = sizeof($cardIDs);
			$queryResults = [];
			for ($x = 0; $x < $max; $x++) {
				$queryString = "Select TimeCardID from [dbo].[TimeCardTb] tc
								Join (Select * from UserTb where UserAppRoleType not in ('Admin', 'ProjectManager', 'Supervisor') and UserActiveFlag = 1 and UserPayMethod = 'H') u on u.UserID = tc.TimeCardTechID
								Where tc.TimeCardStartDate = '" . $data["dateRangeArray"][0] . "' and tc.TimeCardProjectID = " . $cardIDs[$x] . " and tc.TimeCardActiveFlag = 1 and TimeCardPMApprovedFlag != 1";
				$queryResults[$x] = $connection->createCommand($queryString)->queryAll();
			}
			//try to approve time cards
			try {
				$transaction = $connection->beginTransaction();
				$max = sizeof($queryResults);
				for ($x = 0; $x < $max; $x++) {
					$count = sizeof($queryResults[$x]);
					for($i=0; $i < $count; $i++) {
						$statement = "Update TimeCardTb SET TimeCardPMApprovedFlag = 1, TimeCardApprovedBy = '" . $approvedBy . "' WHERE TimeCardID = " . $queryResults[$x][$i]['TimeCardID'];
						$connection->createCommand($statement)->execute();
						//log approvals
						self::logTimeCardHistory(Constants::TIME_CARD_PM_APPROVAL, $queryResults[$x][$i]['TimeCardID']);
					}
				}
				$transaction->commit();
				
				//execute sp to inform accountants if action needs to be taken for submitted cards
				$accountantEmailCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spSendAccountantEmail :StartDate, :EndDate");
				$accountantEmailCommand->bindParam(':StartDate', $data["dateRangeArray"][0],  \PDO::PARAM_STR);
				$accountantEmailCommand->bindParam(':EndDate', $data["dateRangeArray"][1],  \PDO::PARAM_STR);
				$accountantEmailCommand->execute();
				
				$response->setStatusCode(200);
				$response->data = $queryResults;
				return $response;
			} catch(\Exception $e) {
				// if transaction fails rollback changes, archive and send error
				$transaction->rollBack();
				BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				return $response;
			}
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException($e);
		}
	}
	
	/**
	*Call sp to process time card data and generate account files for OASIS, QB, and ADP
	*Looks for JSON PUT body containing date range and project IDs to process
	*@RETURNS JSON w/success flag and comment
	*/
	public function actionAccountantSubmit()
	{
		try
		{
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
			PermissionsController::requirePermission('timeCardSubmit');
			
			//get put data
			$put = file_get_contents("php://input");
			$params = json_decode($put, true);
			
			//format response
			$responseData = [];
			$responseData['success'] = 0;
			$responseData['comments'] = '';
			
			//call function to get file data, check after each file for failure
			$oasisData = self::getSubmissionFileData($params, Constants::OASIS);
			if($oasisData === false)
			{
				$comments = 'Failed to get Oasis Data.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			$payrollData = self::getSubmissionFileData($params, Constants::QUICKBOOKS);
			if($payrollData === false)
			{
				$comments = 'Failed to get Payroll Data.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			$adpData = self::getSubmissionFileData($params, Constants::ADP);
			if($adpData === false)
			{
				$comments = 'Failed to get ADP Data.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			
			//call function to write files, check after each file for failure
			$oasisWriteStatus = count($oasisData) != 0 ? self::writeFileData($oasisData, Constants::OASIS) : true;
			if(!$oasisWriteStatus)
			{
				$comments = 'Failed to write Oasis file.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			$payrollWriteStatus = count($payrollData) != 0 ? self::writeFileData($payrollData, Constants::QUICKBOOKS) : true;
			if(!$payrollWriteStatus)
			{
				$comments = 'Failed to write Payroll file.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			$adpWriteStatus = count($adpData) != 0 ? self::writeFileData($adpData, Constants::ADP) : true;
			if(!$adpWriteStatus)
			{
				$comments = 'Failed to write ADP file.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			
			//if all process run successfully return success
			$responseData['success'] = 1;
			$responseData['comments'] = 'Time Card submission processed successfully.';
			$response = Yii::$app->response;
			$response->data = $responseData;
			$response->format = Response::FORMAT_JSON;
			return $response;
			
		} catch(ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
        }	
	}
	
	private static function getSubmissionFileData($params, $type)
	{
		try{
			$projectIDs = $params['params']['projectIDArray'];
			$startDate = $params['params']['startDate'];
			$endDate = $params['params']['endDate'];
			switch ($type) {
				case Constants::OASIS:
					$spName = 'spGenerateOasisTimeCardByProject';
					$tcEventHistoryType = Constants::TIME_CARD_SUBMISSION_OASIS;
					break;
				case Constants::QUICKBOOKS:
					$spName = 'spGenerateMSDynamicsTimeCardByProject';
					$tcEventHistoryType = Constants::TIME_CARD_SUBMISSION_QB;
					break;
				case Constants::ADP:
					$spName = 'spGenerateADPTimeCardByProject';
					$tcEventHistoryType = Constants::TIME_CARD_SUBMISSION_ADP;
					break;
			}
			
			//if sp call fails concat sp name instead
			$db = BaseActiveRecord::getDb();
			$getFileDataCommand = $db->createCommand("SET NOCOUNT ON EXECUTE $spName :projectIDs, :startDate, :endDate");
			$getFileDataCommand->bindParam(':projectIDs', $projectIDs, \PDO::PARAM_STR);
			$getFileDataCommand->bindParam(':startDate', $startDate, \PDO::PARAM_STR);
			$getFileDataCommand->bindParam(':endDate', $endDate, \PDO::PARAM_STR);
			$fileData = $getFileDataCommand->query();

			//log submission
			self::logTimeCardHistory($tcEventHistoryType, null, $startDate, $endDate);
			
			return $fileData;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'getSubmissionFileData',
				$e,
				getallheaders()['X-Client'],
				'Params: ' . json_encode($params),
				'Type: ' . $type
			);
			return false;
		}
	}
	
	private static function writeFileData($data, $type)
	{
		try {
			switch ($type) {
				case Constants::OASIS:
					$fileNamePrefix = Constants::OASIS_FILE_NAME;
					break;
				case Constants::QUICKBOOKS:
					$fileNamePrefix = Constants::PAYROLL_FILE_NAME;
					break;
				case Constants::ADP:
					$fileNamePrefix = Constants::ADP_FILE_NAME;
					break;
			}
			//get date and format for file name
			$date = BaseActiveController::getDate();
			$formatedDate = str_replace([' ', ':'], '_', $date);
			$fileName = $fileNamePrefix . $formatedDate;
			
			//data is the sp response for the given file, file name payroll_history_2018-03-27_9_36_36.csv, type is type of file being written
			BaseActiveController::processAndWriteCsv($data,$fileName,$type);
			return true;
		}catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'writeFileData',
				$e,
				getallheaders()['X-Client'],
				'Data: ' . json_encode($data),
				'Type: ' . $type
			);
			return false;
		}
	}
	
	private static function resetSubmission($params, $process = 'ALL', $comments = null)
    {
        try{
			$projectIDs = $params['params']['projectIDArray'];
			$startDate = $params['params']['startDate'];
			$endDate = $params['params']['endDate'];
			
            //set db target headers
          	BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $responseArray = [];

			$responseArray = BaseActiveRecord::getDb();
			$getEventsCommand = $responseArray->createCommand("SET NOCOUNT ON EXECUTE spResetSubmitFlag :projectIDs, :startDate, :endDate, :process");
			$getEventsCommand->bindParam(':projectIDs', $projectIDs,  \PDO::PARAM_STR);
			$getEventsCommand->bindParam(':startDate', $startDate,  \PDO::PARAM_STR);
			$getEventsCommand->bindParam(':endDate', $endDate,  \PDO::PARAM_STR);
			$getEventsCommand->bindParam(':process', $process,  \PDO::PARAM_STR);
			$responseArray = $getEventsCommand->query();  

			$status['success'] = true;	
			$response->data = $status;	
			
			//log submission
			self::logTimeCardHistory(Constants::TIME_CARD_SUBMISSION_RESET, null, $startDate, $endDate, $comments);
			
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'resetSubmission',
				$e,
				getallheaders()['X-Client'],
				'Params: ' . json_encode($params),
				'Comments: ' . $comments
			);
			throw new \yii\web\HttpException(400);
		}
    }

    /**
     * Check if there is at least one time card to be been approved
     * @param $timeCardsArr
     * @return boolean
     */
    private function CheckUnapprovedTimeCardExist($timeCardsArr){
        $unapprovedTimeCardExist = false;
        foreach ($timeCardsArr as $item){
            if ($item['TimeCardApprovedFlag'] == 0){
                $unapprovedTimeCardExist = true;
                break;
            }
        }
        return $unapprovedTimeCardExist;
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
			//RBAC permissions check
			PermissionsController::requirePermission('checkSubmitButtonStatus');

            //get body data
            $data = file_get_contents("php://input");
			$submitCheckData = json_decode($data, true)['submitCheck'];
			$isAccountant = isset($submitCheckData['isAccountant']) ? $submitCheckData['isAccountant'] : FALSE;
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
			if($isAccountant) {
	            $responseArray->select('*')
					->from(["fnSubmitAccountant(:StartDate , :EndDate)"])
					->addParams([
						//':ProjectName' => json_encode($projectName), 
						':StartDate' => $submitCheckData['StartDate'], 
						':EndDate' => $submitCheckData['EndDate']]);
			} else {
				$responseArray->select('*')
                ->from(["fnSubmitPM(:ProjectName, :StartDate , :EndDate)"])
                ->addParams([
					':ProjectName' => json_encode($projectName), 
					':StartDate' => $submitCheckData['StartDate'], 
					':EndDate' => $submitCheckData['EndDate']
					]);
			}
            $submitButtonStatus = $responseArray->one(BaseActiveRecord::getDb());
            $responseArray = $submitButtonStatus;

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $responseArray;

            return $response;
        } catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
    }
	
	private function extractProjectsFromTimeCards($dropdownRecords, $projectAllOption)
	{
		$allTheProjects = [];
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
	
	private function extractEmployeesFromTimeCards($dropdownRecords)
	{
		$employeeValues = [];
		//iterate and stash user values
		foreach ($dropdownRecords as $e) {
			//build key value pair
			$key = $e['UserID'];
			$value = $e['UserFullName'];
			$employeeValues[$key] = $value;
		}
		//remove dupes
		$employeeValues = array_unique($employeeValues);
		//abc order for all
		asort($employeeValues);
		//append all option to the front
		$employeeValues = [""=>"All"] + $employeeValues;
		
		return $employeeValues;
	}
	
	private function logTimeCardHistory($type, $timeCardID = null, $startDate = null, $endDate = null, $comments = null)
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
			$historyRecord->Comments = $comments;
			
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
