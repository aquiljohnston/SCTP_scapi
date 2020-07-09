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
use app\modules\v3\controllers\NotificationController;
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
class TimeCardController extends BaseCardController
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
					'accountant-reset' => ['put'],
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

	public function actionShowEntries($cardID){
		try{
			//set db target
			TimeCard::setClient(BaseActiveController::urlPrefix());

			// RBAC permission check
			PermissionsController::requirePermission('timeCardGetEntries');

			$dataArray = [];
			
			//TODO transaction?

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
			
			$lunchQuery = new Query;
			$lunchQuery->select('*')
				->from("fnLunchActivityByTimeCard(:cardID)")
				->addParams([':cardID' => $cardID]);
			$lunchEntries = $lunchQuery->all(BaseActiveRecord::getDb());
			
			$dataArray['card'] = $card;
			$dataArray['show-entries'] = $entries;
			$dataArray['lunch-entries'] = $lunchEntries;			

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $dataArray;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}

    public function actionGetCards($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $clientID = null, $projectID = null,
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
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();

            //format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //response array of time cards//
            $timeCardsArr = [];
            $responseArray = [];
			$allOption = [];
			$showProjectDropDown = false;

            //build base query
            $timeCards = new Query;
            $timeCards->select('*')
                ->from(["fnTimeCardByDate_new(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate]);

            //if is scct website get all or own
            if(BaseActiveController::isSCCT($client)){
				//set project dropdown to true for scct
				$showProjectDropDown = true;
				//rbac permission check
				if (PermissionsController::can('timeCardGetAllCards')){
					$allOption = [""=>"All"];
				}elseif(PermissionsController::can('timeCardGetOwnCards')){
                    $userID = self::getUserFromToken()->UserID;
                    //get user project relations array
                    $projects = ProjectUser::find()
                        ->where("ProjUserUserID = $userID")
                        ->all();
                    $projectsSize = count($projects);
                    if($projectsSize > 0){
                        $timeCards->where(['TimeCardProjectID' => $projects[0]->ProjUserProjectID]);
                    }else{
						//can only get own but has no project relations
						throw new ForbiddenHttpException;
					}if($projectsSize > 1){
						//add all option to project dropdown if there will be more than one option
						$allOption = [""=>"All"];
                        for($i=1; $i < $projectsSize; $i++){
                            $relatedProjectID = $projects[$i]->ProjUserProjectID;
							//could be an 'IN' instead
                            $timeCards->orWhere(['TimeCardProjectID'=>$relatedProjectID]);
                        }
                    }	
                }else{
					//no permissions to get cards
                    throw new ForbiddenHttpException;
				}
            }else{ // get only cards for the current project.
                //get project based on client header
                $project = Project::find()
                    ->where(['ProjectUrlPrefix' => $client])
                    ->one();
                //add project where to query
                $timeCards->where(['TimeCardProjectID' => $project->ProjectID]);
            }
			
			//get client records post user/permissions filter for client dropdown(timing for this execution is very important)
			$clientQuery = clone $timeCards;
			$clientRecords = $clientQuery->select(['ClientName', 'ClientID'])->distinct()->all(BaseActiveRecord::getDb());
			
			//apply client filter
            if($clientID!= null && isset($timeCards)) {
                $timeCards->andFilterWhere([
                    'and',
                    ['ClientID' => $clientID],
                ]);
            }

			//get project records post client filter for project dropdown(timing for this execution is very important)
			$projectQuery = clone $timeCards;
			$projectRecords = $projectQuery->select(['ProjectName', 'TimeCardProjectID'])->distinct()->all(BaseActiveRecord::getDb());

			//apply project filter
            if($projectID!= null && isset($timeCards)) {
                $timeCards->andFilterWhere([
                    'and',
                    ['TimeCardProjectID' => $projectID],
                ]);
            }
			
			//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
			$employeeRecordsQuery = clone $timeCards;
			$employeeRecords = $employeeRecordsQuery->select(['UserID', 'UserFullName'])->distinct()->all(BaseActiveRecord::getDb());
			$approvedStatusQuery = clone $timeCards;
			$approvedStatus = $employeeRecordsQuery->select('TimeCardApprovedFlag')->distinct()->all(BaseActiveRecord::getDb());
			
			
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
				for($i = 0; $i < count($filterArray); $i++){
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
			$clientDropDown = self::extractClientFromCards($clientRecords, $allOption);
			
			//get project list for dropdown based on time cards available
			$projectDropDown = self::extractProjectsFromCards('TimeCard', $projectRecords, $allOption);
			
			//get employee list for dropdown based on time cards available
			$employeeDropDown = self::extractEmployeesFromCards($employeeRecords);
			
			//check if any unapproved cards exist in project filtered records
			$unapprovedTimeCardInProject = $this->checkUnapprovedCardExist('TimeCard', $approvedStatus);

            $paginationResponse = self::paginationProcessor($timeCards, $page, $listPerPage);
            $timeCardsArr = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());
            //check if approved time card exist in the data
            $unapprovedTimeCardVisible = $this->checkUnapprovedCardExist('TimeCard', $timeCardsArr);
            $projectWasSubmitted   = $this->checkAllAssetsSubmitted('TimeCard', $timeCardsArr);
            
			$transaction->commit();
			
            $responseArray['assets'] = $timeCardsArr;
            $responseArray['pages'] = $paginationResponse['pages'];
			$responseArray['clientDropDown'] = $clientDropDown;
            $responseArray['projectDropDown'] = $projectDropDown;
            $responseArray['employeeDropDown'] = $employeeDropDown;
            $responseArray['showProjectDropDown'] = $showProjectDropDown;
			$responseArray['unapprovedTimeCardInProject'] = $unapprovedTimeCardInProject;
			$responseArray['unapprovedTimeCardVisible'] = $unapprovedTimeCardVisible;
            $responseArray['projectSubmitted'] = $projectWasSubmitted;
			$response->data = $responseArray;
			$response->setStatusCode(200);
			return $response;
        }catch(ForbiddenHttpException $e) {
			throw $e;
		}catch(\Exception $e){
		   throw new \yii\web\HttpException(400);
		}
    }

	public function actionGetAccountantView($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $clientID = null, $projectID = null,
		$sortField = 'ProjectName', $sortOrder = 'ASC', $employeeID = null)
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
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();

            //format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

			//response array of time cards
            $timeCards = [];
            $responseArray = [];
			$allOption = [""=>"All"];
			$showProjectDropDown = true;
			//used to get current week if date range falls in the middle of the week
			$sevenDaysPriorToEnd = date('m/d/Y', strtotime($endDate . ' -7 days'));

			//build base query
            $cardQuery = AccountantSubmit::find()
				->select(['ProjectName', 
					'ProjectManager',
					'StartDate',
					'EndDate',
					'ApprovedBy',
					'[Total Time Cards]',
					'[Approved Time Cards]',
					'MSDynamicsSubmitted',
					'OasisSubmitted',
					'ADPSubmitted',
					'ProjectID'])
				->distinct()
				->where(['between', 'StartDate', $startDate, $endDate])
                ->orWhere(['between', 'EndDate', $startDate, $endDate])
                ->orWhere(['between', 'StartDate', $sevenDaysPriorToEnd, $endDate]);

			//get client records post user/permissions filter for client dropdown(timing for this execution is very important)
			$clientQuery = clone $cardQuery;
			$clientRecords = $clientQuery->select(['ClientName', 'ClientID'])->distinct()->all(BaseActiveRecord::getDb());
			
			//apply client filter
            if($clientID!= null && isset($cardQuery)) {
                $cardQuery->andFilterWhere([
                    'and',
                    ['ClientID' => $clientID],
                ]);
            }

			//get project records post client filter for project dropdown(timing for this execution is very important)
			$projectQuery = clone $cardQuery;
			$projectRecords = $projectQuery->select(['ProjectName', 'ProjectID'])->distinct()->all(BaseActiveRecord::getDb());

			//apply project filter
            if($projectID!= null && isset($cardQuery)) {
                $cardQuery->andFilterWhere([
                    'and',
                    ['ProjectID' => $projectID],
                ]);
            }
			
			//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
			$employeeRecordsQuery = clone $cardQuery;
			$employeeRecords = $employeeRecordsQuery->select(['UserID', 'UserFullName'])->distinct()->all(BaseActiveRecord::getDb());
			
			//apply employee filter
			if($employeeID!= null && isset($cardQuery)) {
                $cardQuery->andFilterWhere([
                    'and',
                    ['UserID' => $employeeID],
                ]);
            }
			
			if($filterArray!= null){
				//initialize array for filter query values
				$filterQueryArray = array('or');
				//loop for multi search
				for($i = 0; $i < count($filterArray); $i++){
					//remove leading space from filter string
					$trimmedFilter = trim($filterArray[$i]);
					array_push($filterQueryArray,
						['like', 'ProjectName', $trimmedFilter],
						['like', 'ProjectManager', $trimmedFilter],
						['like', 'ApprovedBy', $trimmedFilter],
						['like', 'UserFullName', $trimmedFilter]
					);
				}
				$cardQuery->andFilterWhere($filterQueryArray);
            }

			//get project list for dropdown based on time cards available
			$clientDropDown = self::extractClientFromCards($clientRecords, $allOption);
			
			//get project list for dropdown based on time cards available
			$projectDropDown = self::extractProjectsFromCards('TimeCard', $projectRecords, $allOption);
			
			//get employee list for dropdown based on time cards available
			$employeeDropDown = self::extractEmployeesFromCards($employeeRecords);

			//paginate
			$paginationResponse = self::paginationProcessor($cardQuery, $page, $listPerPage);
            $timeCards = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());

			//copying this functionality from get cards route, want to look into a way to integrate this with the regular submit check
			//this check seems to have some issue and is only currently being applied to the post filter data set.
			$projectWasSubmitted   = $this->checkAllAssetsSubmitted('TimeCard', $timeCards);

			$transaction->commit();
	
            $responseArray['assets'] = $timeCards;
            $responseArray['pages'] = $paginationResponse['pages'];
            $responseArray['clientDropDown'] = $clientDropDown;
            $responseArray['projectDropDown'] = $projectDropDown;
            $responseArray['employeeDropDown'] = $employeeDropDown;
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

	public function actionGetAccountantDetails($projectID, $startDate, $endDate, $filter = null, $employeeID = null){
		try{
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
			PermissionsController::requirePermission('timeCardGetAccountantDetails');

			$timeCards = self::getCardsByProject($projectID, $startDate, $endDate, Constants::NOTIFICATION_TYPE_TIME, $filter, $employeeID);

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
								Join (Select * from UserTb where UserAppRoleType not in ('Admin', 'ProjectManager', 'Supervisor') and UserActiveFlag = 1 and UserPayMethod in ('H', 'C')) u on u.UserID = tc.TimeCardTechID
								Where tc.TimeCardStartDate = '" . $data["dateRangeArray"][0] . "' and tc.TimeCardProjectID = " . $cardIDs[$x] . " and tc.TimeCardApprovedFlag = 1 and TimeCardPMApprovedFlag != 1";
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
	*Call sp to process time card data and generate account files for OASIS, MSDYNAMICS, and ADP
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
			$payrollData = self::getSubmissionFileData($params, Constants::MSDYNAMICS_TIMECARD);
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
			$payrollWriteStatus = count($payrollData) != 0 ? self::writeFileData($payrollData, Constants::MSDYNAMICS_TIMECARD) : true;
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
				case Constants::MSDYNAMICS_TIMECARD:
					$spName = 'spGenerateMSDynamicsTimeCardByProject';
					$tcEventHistoryType = Constants::TIME_CARD_SUBMISSION_MSDYNAMICS;
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
				case Constants::MSDYNAMICS_TIMECARD:
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
			
			//default check to false
			$submitButtonStatus = 0;
			//RBAC permissions check
			if(PermissionsController::can('checkSubmitButtonStatus')){
				//get body data
				$data = file_get_contents("php://input");
				$submitCheckData = json_decode($data, true)['submitCheck'];
				$isAccountant = isset($submitCheckData['isAccountant']) ? $submitCheckData['isAccountant'] : FALSE;
				//if is not scct project name will always be the current client
				if(BaseActiveController::isSCCT($client)){
					$projectName  = $submitCheckData['ProjectName'];
				}else{
					$project = Project::find()
						->where(['ProjectUrlPrefix' => $client])
						->one();
					$projectName = array($project->ProjectID);
				}

				//build base query
				$checkQuery = new Query;
				if($isAccountant) {
					$checkQuery->select('*')
						->from(["fnSubmitAccountant(:StartDate , :EndDate)"])
						->addParams([
							//':ProjectName' => json_encode($projectName), 
							':StartDate' => $submitCheckData['StartDate'], 
							':EndDate' => $submitCheckData['EndDate']]);
				} else {
					$checkQuery->select('*')
					->from(["fnSubmitPM(:ProjectName, :StartDate , :EndDate)"])
					->addParams([
							':ProjectName' => json_encode($projectName), 
							':StartDate' => $submitCheckData['StartDate'], 
							':EndDate' => $submitCheckData['EndDate']
						]);
				}
				$submitButtonStatus = $checkQuery->one(BaseActiveRecord::getDb());
			}

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $submitButtonStatus;

            return $response;
        } catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
    }
	
	public function actionAccountantReset(){
		try{			
			$put = file_get_contents("php://input");
			$params = json_decode($put, true);
			$startDate = $params['dates']['startDate'];
			$endDate = $params['dates']['endDate'];
		
            //set db target headers
          	BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

			$connection = BaseActiveRecord::getDb();
			$resetCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spResetTimeCardSubmitFlag :startDate, :endDate");
			$resetCommand->bindParam(':startDate', $startDate,  \PDO::PARAM_STR);
			$resetCommand->bindParam(':endDate', $endDate,  \PDO::PARAM_STR);
			$resetCommand->execute();  
			
			//log submission
			self::logTimeCardHistory(Constants::TIME_CARD_ACCOUNTANT_RESET, null, $startDate, $endDate);
			
			$status['success'] = true;
			$response->data = $status;	
			return $response;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'accountantResetTime',
				$e,
				getallheaders()['X-Client'],
				'Start Date: ' . $startDate,
				'End Date: ' . $endDate
			);
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionPMReset(){
		try{			
			$put = file_get_contents("php://input");
			$jsonArray = json_decode($put, true);
			$data = $jsonArray['data'];
			
			//set db target headers
          	BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			
			$timeCardIDs = [];
			//get user
			$user = self::getUserFromToken();
			$username = $user->UserName;
			
			//archive json
			BaseActiveController::archiveWebJson($put, Constants::TIME_CARD_PM_RESET, $username, BaseActiveController::urlPrefix());
			
			//fetch all time cards for selected rows
			for($i = 0; $i < count($data); $i++){
				$projectID = $data[$i]['ProjectID'];
				$startDate = $data[$i]['StartDate'];
				$endDate = $data[$i]['EndDate'];
				$newCards = self::getCardsByProject($projectID, $startDate, $endDate, Constants::NOTIFICATION_TYPE_TIME);
				$newCards = array_column($newCards, 'TimeCardID');
				$timeCardIDs = array_merge($timeCardIDs, $newCards);
			}
			//encode array to pass to sp
			$timeCardIDs = json_encode($timeCardIDs);

			$connection = BaseActiveRecord::getDb();
			$resetCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spTimeCardResetPMApprovedFlag :TimeCardIDJSON, :RequestedBy");
			$resetCommand->bindParam(':TimeCardIDJSON', $timeCardIDs,  \PDO::PARAM_STR);
			$resetCommand->bindParam(':RequestedBy', $username,  \PDO::PARAM_STR);
			$resetCommand->execute(); 

			//create new notification
			NotificationController::create(
				Constants::NOTIFICATION_TYPE_TIME,
				$timeCardIDs,
				Constants::NOTIFICATION_DESCRIPTION_RESET_PM_TIME,
				Constants::APP_ROLE_PROJECT_MANAGER,
				$username);
			
			$status['success'] = true;
			$response->data = $status;	
			return $response;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				Constants::TIME_CARD_PM_RESET,
				$e,
				getallheaders()['X-Client']
			);
			throw new \yii\web\HttpException(400);
		}
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
