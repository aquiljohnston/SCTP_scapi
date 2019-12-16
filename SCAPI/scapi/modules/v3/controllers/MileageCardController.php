<?php

namespace app\modules\v3\controllers;

use app\modules\v3\constants\Constants;
use app\modules\v3\controllers\CreateMethodNotAllowed;
use app\modules\v3\controllers\DeleteMethodNotAllowed;
use app\modules\v3\controllers\PermissionsController;
use app\modules\v3\controllers\UpdateMethodNotAllowed;
use Yii;
use app\modules\v3\models\MileageCard;
use app\modules\v3\models\MileageEntry;
use app\modules\v3\models\Project;
use app\modules\v3\models\ProjectUser;
use app\modules\v3\models\AllMileageCardsCurrentWeek;
use app\modules\v3\models\MileageCardAccountantSubmit;
use app\modules\v3\models\MileageCardEventHistory;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\controllers\NotificationController;
use app\modules\v3\authentication\TokenAuth;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\data\Pagination;
use yii\db\query;

/**
 * MileageCardController implements the CRUD actions for MileageCard model.
 */
class MileageCardController extends BaseCardController
{
    public $modelClass = 'app\modules\v3\models\MileageCard'; 
	
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
		try{
			//set db target
			MileageCard::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageCardApprove');
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			//get user id
			$approvedBy = self::getUserFromToken()->UserName;

			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Mileage Card Approve', $approvedBy, BaseActiveController::urlPrefix());
			
			//parse json
			$cardIDs = $data["cardIDArray"];
			$approvedCards = []; //Prevent uninitialized error
			//get mielagecards
			foreach($cardIDs as $id)
			{
				$approvedCards[]= MileageCard::findOne($id);
			}
			
			//try to approve time cards
			try
			{
				//create transaction
                $connection = MileageCard::getDb();
				$transaction = $connection->beginTransaction(); 
			
				foreach($approvedCards as $card)
				{
					$card-> MileageCardApprovedFlag = 1;
					$card-> MileageCardApprovedBy = $approvedBy;
					$card-> MileageCardModifiedDate = Parent::getDate();
					if(!$card-> update()){
						throw BaseActiveController::modelValidationException($card);
					}
					//log approvals
					self::logMileageCardHistory(Constants::MILEAGE_CARD_APPROVAL, $card->MileageCardID);
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
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();

			// RBAC permission check
			PermissionsController::requirePermission('mileageCardGetEntries');

			$dataArray = [];

			$entriesQuery = new Query;
			$entriesQuery->select('*')
				->from("fnMileageCardEntrysByMileageCard(:cardID)")
				->addParams([':cardID' => $cardID]);
			$entries = $entriesQuery->all(BaseActiveRecord::getDb());

			$cardQuery = new Query;
			$cardQuery->select('*')
				->from("fnMileageCardByID(:cardID)")
				->addParams([':cardID' => $cardID]);
			$card = $cardQuery->one(BaseActiveRecord::getDb());
			
			$transaction->commit();

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
			//TODO consider adding global delimiter constant
			$delimiter = ',';
			$filterArray = explode($delimiter, $filter);
			//set start date to last Sunday add a day to prevent issue occuring if current day is sunday
			$startDate = date('Y-m-d',strtotime($startDate . '+1 day'));
			$startDate = date('Y-m-d',strtotime($startDate . 'last sunday'));
			$endDate = date('Y-m-d',strtotime($endDate . '-1 day'));
			$endDate = date('Y-m-d',strtotime($endDate . 'next saturday'));
			
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//response array of mileage cards
            $mileageCardsArr = [];
            $responseArray = [];
			$projectAllOption = [];
			$allProjects = [];
			$showProjectDropDown = false;
			
			//build base query
			$mileageCards = new Query;
			$mileageCards->select('*')
				->from(["fnMileageCardByDate(:startDate, :endDate)"])
				->addParams([':startDate' => $startDate, ':endDate' => $endDate]);
			
			//if is scct website get all or own
			if(BaseActiveController::isSCCT($client)){
				//set project dropdown to true for scct
				$showProjectDropDown = true;
				//rbac permission check
				if (PermissionsController::can('mileageCardGetAllCards')){
					$projectAllOption = [""=>"All"];
                }elseif(PermissionsController::can('mileageCardGetOwnCards')){
					$userID = self::getUserFromToken()->UserID;
					//get user project relations array
					$projects = ProjectUser::find()
						->where("ProjUserUserID = $userID")
						->all();
					$projectsSize = count($projects);
					if($projectsSize > 0){
						$mileageCards->where(['MileageCardProjectID' => $projects[0]->ProjUserProjectID]);
					}else{
						//can only get own but has no project relations
						throw new ForbiddenHttpException;
					}if($projectsSize > 1){
						//add all option to project dropdown if there will be more than one option
						$projectAllOption = [""=>"All"];
                        for($i=1; $i < $projectsSize; $i++){
                            $relatedProjectID = $projects[$i]->ProjUserProjectID;
							//could be an 'IN' instead
                            $mileageCards->orWhere(['MileageCardProjectID'=>$relatedProjectID]);
                        }
                    }
				}else{
					//no permissions for any cards
					throw new ForbiddenHttpException;
				}
			}else{ // get only cards for the current project.
				//get project based on client header
				$project = Project::find()
					->where(['ProjectUrlPrefix' => $client])
					->one();
				//add project where to query
				$mileageCards->where(['MileageCardProjectID' => $project->ProjectID]);
			}
			
			//get records post user/permissions filter for project dropdown(timing for this execution is very important)
			$preFilteredRecords = $mileageCards->all(BaseActiveRecord::getDb());

			//apply project filter
            if($projectID!= null && isset($mileageCards)) {
                $mileageCards->andFilterWhere([
                    'and',
                    ['MileageCardProjectID' => $projectID],
                ]);
				//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
				$projectFilteredRecords = $mileageCards->all(BaseActiveRecord::getDb());
            }else{
				$projectFilteredRecords = $preFilteredRecords;
			}
			
			//apply employee filter
			if($employeeID!= null && isset($mileageCards)) {
                $mileageCards->andFilterWhere([
                    'and',
                    ['UserID' => $employeeID],
                ]);
            }

            if($filterArray != null && isset($mileageCards)){ //Empty strings or nulls will result in false
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
				$mileageCards->andFilterWhere($filterQueryArray);
            }
			
			//get project list for dropdown based on time cards available
			$projectDropDown = self::extractProjectsFromCards('MileageCard', $preFilteredRecords, $projectAllOption);
			
			//get employee list for dropdown based on time cards available
			$employeeDropDown = self::extractEmployeesFromCards($projectFilteredRecords);
			
			//check if any unapproved cards exist in project filtered records
            $unapprovedMileageCardInProject = $this->checkUnapprovedCardExist('MileageCard', $projectFilteredRecords);
			
			//add pagination and fetch mileage card data
            $paginationResponse = BaseActiveController::paginationProcessor($mileageCards, $page, $listPerPage);
            $mileageCardsArr = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());
			
			//check mileage card submission statuses
            $unapprovedMileageCardVisible = $this->checkUnapprovedCardExist('MileageCard', $mileageCardsArr);

			//unsure of business rules for mileage submission
            $projectWasSubmitted = $this->checkAllAssetsSubmitted('MileageCard', $mileageCardsArr);
			
			$transaction->commit();
			
            $responseArray['assets'] = $mileageCardsArr;
            $responseArray['pages'] = $paginationResponse['pages'];
			$responseArray['projectDropDown'] = $projectDropDown;
            $responseArray['employeeDropDown'] = $employeeDropDown;
            $responseArray['showProjectDropDown'] = $showProjectDropDown;
			$responseArray['unapprovedMileageCardInProject'] = $unapprovedMileageCardInProject;
			$responseArray['unapprovedMileageCardVisible'] = $unapprovedMileageCardVisible;
            $responseArray['projectSubmitted'] = $projectWasSubmitted;
			$response->data = $responseArray;
			$response->setStatusCode(200);
			return $response;
		} catch (ForbiddenHttpException $e) {
            throw $e;
        } catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionGetAccountantView($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $projectID = null,
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
			PermissionsController::requirePermission('mileageCardGetAccountantView');

            //format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

			//response array of mileage cards
            $mileageCards = [];
            $responseArray = [];
			$projectDropDown = [""=>"All"];
			$showProjectDropDown = true;
			//used to get current week if date range falls in the middle of the week
			$sevenDaysPriorToEnd = date('m/d/Y', strtotime($endDate . ' -7 days'));

			//build base query
            $cardQuery = MileageCardAccountantSubmit::find()
				->select(['ProjectName', 
					'ProjectManager',
					'StartDate',
					'EndDate',
					'ApprovedBy',
					'[Total Mileage Cards]',
					'[Approved Mileage Cards]',
					'MSDynamicsSubmitted',
					'OasisSubmitted',
					'ADPSubmitted',
					'ProjectID'])
				->distinct()
				->where(['between', 'StartDate', $startDate, $endDate])
                ->orWhere(['between', 'EndDate', $startDate, $endDate])
                ->orWhere(['between', 'StartDate', $sevenDaysPriorToEnd, $endDate]);

			//get records for project dropdown(timing for this execution is very important)
			$projectDropdownRecords = clone $cardQuery;
			
			//add project filter
			if($projectID!= null){
                $cardQuery->andFilterWhere([
                    'and',
                    ['ProjectID' => $projectID],
                ]);
				//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
				$employeeDropdownRecords = clone $cardQuery;
            }else{
				$employeeDropdownRecords = clone $projectDropdownRecords;
			}

			//complete queries for projects and employees
            $projectDropdownRecords = $projectDropdownRecords->all(BaseActiveRecord::getDb());
            $employeeDropdownRecords = $employeeDropdownRecords->addSelect(['UserFullName', 'UserID'])->all(BaseActiveRecord::getDb());
			
			//apply employee filter
			if($employeeID!= null) {
                $cardQuery->andFilterWhere([
                    'and',
                    ['UserID' => $employeeID],
                ]);
            }

			//add search filter
			if($filter != null){
                $cardQuery->andFilterWhere([
                    'or',
                    ['like', 'ProjectName', $filter],
                    ['like', 'ProjectManager', $filter],
                    ['like', 'ApprovedBy', $filter],
                    ['like', 'UserFullName', $filter],
                ]);
            }

			//get project list for dropdown based on time cards available
			$projectDropDown = self::extractProjectsFromCards('MileageCard', $projectDropdownRecords, $projectDropDown);
			
			//get employee list for dropdown based on time cards available
			$employeeDropDown = self::extractEmployeesFromCards($employeeDropdownRecords);

			//paginate
			$paginationResponse = self::paginationProcessor($cardQuery, $page, $listPerPage);
            $mileageCards = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());

			//copying this functionality from get cards route, want to look into a way to integrate this with the regular submit check
			//this check seems to have some issue and is only currently being applied to the post filter data set.
			$projectWasSubmitted   = $this->checkAllAssetsSubmitted('MileageCard', $mileageCards);

            $responseArray['assets'] = $mileageCards;
            $responseArray['pages'] = $paginationResponse['pages'];
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

	public function actionGetAccountantDetails($projectID, $startDate, $endDate, $filter = null, $employeeID = null)
	{
		try{
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
			PermissionsController::requirePermission('mileageCardGetAccountantDetails');

			$mileageCards = self::getCardsByProject($projectID, $startDate, $endDate, Constants::NOTIFICATION_TYPE_MILEAGE, $filter, $employeeID);

			//format response
			$responseArray['details'] = $mileageCards;
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
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageCardPmSubmit');
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get userid
			$approvedBy = self::getUserFromToken()->UserName;
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Mileage Card Submittal', $approvedBy, BaseActiveController::urlPrefix());
			
			//parse json	
			$cardIDs = $data["projectIDArray"];
			$connection = BaseActiveRecord::getDb();
			// get all mileagecards
			$max = sizeof($cardIDs);
			$queryResults = [];
			for ($x = 0; $x < $max; $x++) {
				$queryString = "Select MileageCardID from [dbo].[MileageCardTb] mc
								Join (Select * from UserTb where UserAppRoleType not in ('Admin', 'ProjectManager', 'Supervisor') and UserActiveFlag = 1 and UserPayMethod in ('H', 'C')) u on u.UserID = mc.MileageCardTechID
								Where mc.MileageStartDate = '" . $data["dateRangeArray"][0] . "' and mc.MileageCardProjectID = " . $cardIDs[$x] . " and mc.MileageCardActiveFlag = 1 and MileageCardPMApprovedFlag != 1";
				$queryResults[$x] = $connection->createCommand($queryString)->queryAll();	
			}
			//try to approve mileage cards
			try {
				$transaction = $connection->beginTransaction();
				$max = sizeof($queryResults);
				for ($x = 0; $x < $max; $x++) {
					$count = sizeof($queryResults[$x]);
					for($i=0; $i < $count; $i++) {
						$statement = "Update MileageCardTb SET MileageCardPMApprovedFlag = 1, MileageCardApprovedBy = '" . $approvedBy . "' WHERE MileageCardID = " . $queryResults[$x][$i]['MileageCardID'];
						$connection->createCommand($statement)->execute();
						//log approvals
						self::logMileageCardHistory(Constants::MILEAGE_CARD_PM_APPROVAL, $queryResults[$x][$i]['MileageCardID']);
					}
				}
				$transaction->commit();
				
				//execute sp to inform accountants if action needs to be taken for submitted cards
				$accountantEmailCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spSendMileageCardAccountantEmail :StartDate, :EndDate");
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
	*Call sp to process mileage card data and generate account files for OASIS and MSDYNAMICS
	*Looks for JSON PUT body containing date range and project IDs to process
	*@RETURNS JSON w/success flag and comment
	*TODO consider extracting some submit functions out when time card v3 is created.
	*/
	public function actionAccountantSubmit()
	{
		try{
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
			PermissionsController::requirePermission('mileageCardSubmit');
			
			//get put data
			$put = file_get_contents("php://input");
			$params = json_decode($put, true);
			
			//format response
			$response = Yii::$app->response;
			$responseData = [];
			$responseData['success'] = 0;
			$responseData['comments'] = '';
			
			//call function to get file data, check after each file for failure
			$oasisData = self::getSubmissionFileData($params, Constants::MILEAGE_CARD_OASIS);
			if($oasisData === false)
			{
				$comments = 'Failed to get Oasis Data.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				$response->format = Response::FORMAT_JSON;
				return $response;
			}
			$msDynamicsData = self::getSubmissionFileData($params, Constants::MSDYNAMICS_MILEAGECARD);
			if($msDynamicsData === false)
			{
				$comments = 'Failed to get MSDYNAMICS Data.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				$response->format = Response::FORMAT_JSON;
				return $response;
			}
			
			//call function to write files, check after each file for failure
			$oasisWriteStatus = count($oasisData) != 0 ? self::writeFileData($oasisData, Constants::MILEAGE_CARD_OASIS) : true;
			if(!$oasisWriteStatus)
			{
				$comments = 'Failed to write Oasis file.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				$response->format = Response::FORMAT_JSON;
				return $response;
			}
			$msDynamicsWriteStatus = count($msDynamicsData) != 0 ? self::writeFileData($msDynamicsData, Constants::MSDYNAMICS_MILEAGECARD) : true;
			if(!$msDynamicsWriteStatus)
			{
				$comments = 'Failed to write MSDYNAMICS file.';
				self::resetSubmission($params, 'ALL', $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				$response->format = Response::FORMAT_JSON;
				return $response;
			}
			
			//if all process run successfully return success
			$responseData['success'] = 1;
			$responseData['comments'] = 'Mileage Card submission processed successfully.';
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
				case Constants::MILEAGE_CARD_OASIS:
					$spName = 'spGenerateOasisMileageCardByProject';
					$mcEventHistoryType = Constants::MILEAGE_CARD_SUBMISSION_OASIS;
					break;
				case Constants::MSDYNAMICS_MILEAGECARD:
					$spName = 'spGenerateMSDynamicsMileageCardByProject';
					$mcEventHistoryType = Constants::MILEAGE_CARD_SUBMISSION_MSDYNAMICS;
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
			self::logMileageCardHistory($mcEventHistoryType, null, $startDate, $endDate);
			
			return $fileData;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'getMileageSubmissionFileData',
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
				case Constants::MILEAGE_CARD_OASIS:
					$fileNamePrefix = Constants::OASIS_MILEAGE_FILE_NAME;
					break;
				case Constants::MSDYNAMICS_MILEAGECARD:
					$fileNamePrefix = Constants::MSDYNAMICS_MILEAGE_FILE_NAME;
					break;
			}
			//get date and format for file name
			$date = BaseActiveController::getDate();
			$formatedDate = str_replace([' ', ':'], '_', $date);
			$fileName = $fileNamePrefix . $formatedDate;
			
			//data is the sp response for the given file, file name oasis_history_2018-03-27_9_36_36.csv, type is type of file being written
			BaseActiveController::processAndWriteCsv($data,$fileName,$type);
			return true;
		}catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'writeMileageFileData',
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
			$getEventsCommand = $responseArray->createCommand("SET NOCOUNT ON EXECUTE spResetMileageCardSubmitFlag :projectIDs, :startDate, :endDate, :process");
			$getEventsCommand->bindParam(':projectIDs', $projectIDs,  \PDO::PARAM_STR);
			$getEventsCommand->bindParam(':startDate', $startDate,  \PDO::PARAM_STR);
			$getEventsCommand->bindParam(':endDate', $endDate,  \PDO::PARAM_STR);
			$getEventsCommand->bindParam(':process', $process,  \PDO::PARAM_STR);
			$responseArray = $getEventsCommand->query();  

			$status['success'] = true;	
			$response->data = $status;	
			
			//log submission
			self::logMileageCardHistory(Constants::MILEAGE_CARD_SUBMISSION_RESET, null, $startDate, $endDate, $comments);
			
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'resetMileageSubmission',
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
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//default check to false
			$submitButtonStatus = 0;
			//RBAC permissions check
			if(PermissionsController::can('checkSubmitButtonStatus')){
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
				$checkQuery = new Query;
				if($isAccountant) {
					$checkQuery->select('*')
						->from(["fnMileageCardSubmitAccountant(:StartDate , :EndDate)"])
						->addParams([
							//':ProjectName' => json_encode($projectName), 
							':StartDate' => $submitCheckData['StartDate'], 
							':EndDate' => $submitCheckData['EndDate']]);
				} else {
					$checkQuery->select('*')
					->from(["fnMileageCardSubmitPM(:ProjectName, :StartDate , :EndDate)"])
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
			$resetCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spResetMileageCardSubmitFlag :startDate, :endDate");
			$resetCommand->bindParam(':startDate', $startDate,  \PDO::PARAM_STR);
			$resetCommand->bindParam(':endDate', $endDate,  \PDO::PARAM_STR);
			$resetCommand->execute();  
			
			//log submission
			self::logMileageCardHistory(Constants::MILEAGE_CARD_ACCOUNTANT_RESET, null, $startDate, $endDate);
			
			$status['success'] = true;
			$response->data = $status;	
			return $response;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'accountantResetMileage',
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
			
			$mileageCardIDs = [];
			//get user
			$user = self::getUserFromToken();
			$username = $user->UserName;
			
			//archive json
			BaseActiveController::archiveWebJson($put, Constants::MILEAGE_CARD_PM_RESET, $username, BaseActiveController::urlPrefix());
			
			//fetch all time cards for selected rows
			for($i = 0; $i < count($data); $i++){
				$projectID = $data[$i]['ProjectID'];
				$startDate = $data[$i]['StartDate'];
				$endDate = $data[$i]['EndDate'];
				$newCards = self::getCardsByProject($projectID, $startDate, $endDate, Constants::NOTIFICATION_TYPE_MILEAGE);
				$newCards = array_column($newCards, 'MileageCardID');
				$mileageCardIDs = array_merge($mileageCardIDs, $newCards);
			}
			//encode array to pass to sp
			$mileageCardIDs = json_encode($mileageCardIDs);

			$connection = BaseActiveRecord::getDb();
			$resetCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spMileageCardResetPMApprovedFlag  :MileageCardIDJSON, :RequestedBy");
			$resetCommand->bindParam(':MileageCardIDJSON', $mileageCardIDs,  \PDO::PARAM_STR);
			$resetCommand->bindParam(':RequestedBy', $username,  \PDO::PARAM_STR);
			$resetCommand->execute();
			
			//create new notification
			NotificationController::create(
			Constants::NOTIFICATION_TYPE_MILEAGE,
			$mileageCardIDs,
			Constants::NOTIFICATION_DESCRIPTION_RESET_PM_MILEAGE,
			Constants::APP_ROLE_PROJECT_MANAGER,
			$username);
			
			$status['success'] = true;
			$response->data = $status;	
			return $response;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				Constants::MILEAGE_CARD_PM_RESET,
				$e,
				getallheaders()['X-Client']
			);
			throw new \yii\web\HttpException(400);
		}
	}
	
	//inserts records into historical tables
	private function logMileageCardHistory($type, $mileageCardID = null, $startDate = null, $endDate = null, $comments = null)
	{
		try
		{
			//create and populate model
			//TODO need table
			$historyRecord = new MileageCardEventHistory;
			$historyRecord->Date = BaseActiveController::getDate();
			$historyRecord->Name = self::getUserFromToken()->UserName;
			$historyRecord->Type = $type;
			$historyRecord->MileageCardID = $mileageCardID;
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
