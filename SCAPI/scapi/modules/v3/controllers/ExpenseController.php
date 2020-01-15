<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\db\Query;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\constants\Constants;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\ProjectUser;
use app\modules\v3\models\Project;
use app\modules\v3\models\BaseUser;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\Expense;
use app\modules\v3\models\GetExpenses;
use app\modules\v3\models\ExpenseEventHistory;
use app\modules\v3\models\ExpenseEntryEventHistory;


class ExpenseController extends Controller{

	public function behaviors(){
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = [
                'class' => VerbFilter::className(),
                'actions' => [
					'get' => ['get'],
					'approve'  => ['put'],
					'deactivate'  => ['put'],
					'get-accountant-view' => ['get'],
					'get-accountant-details' => ['get'],
					'show-entries' => ['get'],
					'get-modal-dropdown' => ['get'],
					'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public static function processExpense($data, $client){
		try{
			//set client header
			BaseActiveRecord::setClient($client);
	
			//try catch to log expense object error
			try{					
				$successFlag = 0;
				$expense = new Expense;
				$expense->attributes = $data;
				$expense->UserID = BaseActiveController::getUserFromToken()->UserID;
				$expense->CreatedDate = $data['CreatedDateTime'];

				if ($expense->save()){
					$successFlag = 1;
				} else {
					throw BaseActiveController::modelValidationException($expense);
				}
			}catch(yii\db\Exception $e){
				//if db exception is 2601, duplicate contraint then success
				if(in_array($e->errorInfo[1], array(2601, 2627))){
					$successFlag = 1;
				}else{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
					$successFlag = 0;
				}
			}
			$responseData = [
				'CreatedDate' => $data['CreatedDateTime'],
				'ChargeAccount' => $data['ChargeAccount'],
				'SuccessFlag' => $successFlag
			];
			//return response data
			return $responseData;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGet($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $projectID = null, $employeeID = null,
		$sortField = 'Username', $sortOrder = 'ASC')
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

            //response array of expenses
            $expensesArr = [];
            $responseArray = [];
			$projectAllOption = [];
			$allTheProjects = [];
			$showProjectDropDown = false;

			$expenses = new Query;
            $expenses->select('*')
                ->from(["fnGetExpensesByDate(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate]);

            //if is scct website get all or own
            if(BaseActiveController::isSCCT($client)){
				//set project dropdown to true for scct
				$showProjectDropDown = true;
				//rbac permission check
				if (PermissionsController::can('expenseGetAll')){
					$projectAllOption = [""=>"All"];
				}elseif(PermissionsController::can('expenseGetOwn')){
                    $userID = BaseActiveController::getUserFromToken()->UserID;
                    //get user project relations array
                    $projects = ProjectUser::find()
                        ->where("ProjUserUserID = $userID")
                        ->all();
                    $projectsSize = count($projects);
                    if($projectsSize > 0){
						//add all option to project dropdown if there will be more than one option
						$projectAllOption = [""=>"All"];
                        $expenses->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
						for($i=1; $i < $projectsSize; $i++){
                            $relatedProjectID = $projects[$i]->ProjUserProjectID;
							//could be an 'IN' instead
                            $expenses->orWhere(['ProjectID'=>$relatedProjectID]);
                        }
                    }else{
						//can only get own but has no project relations
						throw new ForbiddenHttpException;
					}	
                }else{
					//no permissions to get cards
                    throw new ForbiddenHttpException;
				}
            }else{ // get only expenses for the current project.
                //get project based on client header
                $project = Project::find()
                    ->where(['ProjectUrlPrefix' => $client])
                    ->one();
                //add project where to query
                $expenses->where(['ProjectID' => $project->ProjectID]);
            }

			//get records post user/permissions filter for project dropdown(timing for this execution is very important)
			$preFilteredRecords = $expenses->all(BaseActiveRecord::getDb());

			//apply project filter
            if($projectID!= null) {
                $expenses->andFilterWhere([
                    'and',
                    ['ProjectID' => $projectID],
                ]);
				//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
				$projectFilteredRecords = $expenses->all(BaseActiveRecord::getDb());
            }else{
				$projectFilteredRecords = $preFilteredRecords;
			}

			//apply employee filter
			if($employeeID!= null){
                $expenses->andFilterWhere([
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
						['like', 'UserName', $trimmedFilter],
						['like', 'ProjectName', $trimmedFilter],
						['like', 'Quantity', $trimmedFilter],
						['like', 'StartDate', $trimmedFilter],
						['like', 'EndDate', $trimmedFilter]
					);
				}
				$expenses->andFilterWhere($filterQueryArray);
            }
			
			//get project list for dropdown based on expenses available
			$projectDropDown = self::extractProjects($preFilteredRecords, $projectAllOption);
			
			//get employee list for dropdown based on expenses available
			$employeeDropDown = self::extractEmployees($projectFilteredRecords);
			
			//check if any unapproved expenses exist in project filtered records
			$unapprovedExpenseInProject = $this->checkUnapprovedExist($projectFilteredRecords);

            $paginationResponse = BaseActiveController::paginationProcessor($expenses, $page, $listPerPage);
            $expensesArr = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());
            //check if approved time card exist in the data
            $unapprovedExpenseVisible = $this->checkUnapprovedExist($expensesArr);
            $projectWasSubmitted   = $this->checkAllAssetsSubmitted($expensesArr);
            
            $responseArray['assets'] = $expensesArr;
            $responseArray['pages'] = $paginationResponse['pages'];
            $responseArray['projectDropDown'] = $projectDropDown;
            $responseArray['employeeDropDown'] = $employeeDropDown;
            $responseArray['showProjectDropDown'] = $showProjectDropDown;
			$responseArray['unapprovedExpenseInProject'] = $unapprovedExpenseInProject;
			$responseArray['unapprovedExpenseVisible'] = $unapprovedExpenseVisible;
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
	
	public function actionApprove(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			// RBAC permission check
			PermissionsController::requirePermission('expenseApprove');

			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			//get username
			$approvedBy = BaseActiveController::getUserFromToken()->UserName;

			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Expense Approve', $approvedBy, BaseActiveController::urlPrefix());

			//parse json
			$expenses = $data['expenseArray'];
			$approvedExpenses = []; // Prevents empty array from causing crash
			//get expenses
			$approvedExpenses = Expense::find()
				->where(['in', 'ID', $expenses])
				->all();

			//try to approve expenses
			try{
				//create transaction
				$connection = Expense::getDb();
				$transaction = $connection->beginTransaction();

				foreach ($approvedExpenses as $expense){
					$expense->IsApproved = 1;
					$expense->ApprovedBy = $approvedBy;
					$expense->ApprovedDate = BaseActiveController::getDate();
					$expense->update();
					//log approvals
					self::logExpenseHistory(Constants::EXPENSE_APPROVAL, $expense->ID);
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $approvedExpenses;
				return $response;
			}catch(\Exception $e){ //if transaction fails rollback changes and send error
				$transaction->rollBack();
				//archive error
				BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				return $response;
			}
		}catch (ForbiddenHttpException $e){
			throw new ForbiddenHttpException;
		}catch(\Exception $e) {
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionDeactivate(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			// RBAC permission check
			PermissionsController::requirePermission('expenseDeactivate');

			//get date and current user
			$modifiedBy = BaseActiveController::getUserFromToken()->UserName;
			$modifiedDate = BaseActiveController::getDate();
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Expense Deactivate', $modifiedBy, BaseActiveController::urlPrefix());
			
			//parse json
			$expenses = $data['expenseArray'];
			$deactivatedExpenses = []; // Prevents empty array from causing crash
			//get expenses
			$deactivatedExpenses = Expense::find()
				->where(['in', 'ID', $expenses])
				->all();
			$deactivatedHistoryRecords = ExpenseEventHistory::find()
				->where(['in', 'ExpenseID', $expenses])
				->all();
			
			try{
				//remove history record to avoid constraint
				foreach($deactivatedHistoryRecords as $historyRecord){
					$historyRecord->delete();
				}
				foreach($deactivatedExpenses as $expense){
					if(self::createHistoryRecord($expense, $modifiedBy, $modifiedDate,Constants::EXPENSE_DEACTIVATE)){
						//delete the record, to avoid constraint issues
						$expense->delete();
					}
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $deactivatedExpenses;
			}catch(Exception $e){
				$transaction->rollBack();
				//archive error after rollback
				BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
			return $response;
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
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
			PermissionsController::requirePermission('expenseGetAccountantView');

            //format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

			//response array of exepenses
            $expenses = [];
            $responseArray = [];
			$allTheProjects = [""=>"All"];
			$showProjectDropDown = true;

			//build base query
            $expenseQuery = new Query;
            $expenseQuery->select(['ProjectID', 
					'ProjectName',
					'ProjectManager',
					'StartDate',
					'EndDate',
					'ApprovedCount',
					'TotalCount',
					'IsSubmitted'])
				->distinct()
                ->from(["fnGetExpensesByDateGroupByProject(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate]);

			//get records for project dropdown(timing for this execution is very important)
			$projectDropdownRecords = clone $expenseQuery;

			//add project filter
			if($projectID!= null){
                $expenseQuery->andFilterWhere([
                    'and',
                    ['ProjectID' => $projectID],
                ]);
            	//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
				$employeeDropdownRecords = clone $expenseQuery;
            }else{
				$employeeDropdownRecords = clone $projectDropdownRecords;
			}
			
			//complete queries for projects and employees
			$projectDropdownRecords = $projectDropdownRecords->all(BaseActiveRecord::getDb());
			$employeeDropdownRecords = $employeeDropdownRecords->addSelect(['UserName', 'UserID'])->all(BaseActiveRecord::getDb());
			
			//apply employee filter
			if($employeeID!= null) {
                $expenseQuery->andFilterWhere([
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
						['like', 'StartDate', $trimmedFilter],
						['like', 'EndDate', $trimmedFilter],
						['like', 'UserName', $trimmedFilter]
					);
				}
				$expenseQuery->andFilterWhere($filterQueryArray);
            }

			//get project list for dropdown based on time cards available
			$allTheProjects = self::extractProjects($projectDropdownRecords, $allTheProjects);
			
			//get employee list for dropdown based on time cards available
			$employeeDropDown = self::extractEmployees($employeeDropdownRecords);

			//paginate
			$paginationResponse = BaseActiveController::paginationProcessor($expenseQuery, $page, $listPerPage);
            $expenses = $paginationResponse['Query']
				->orderBy("$sortField $sortOrder")
				->all(BaseActiveRecord::getDb());

			//copying this functionality from get cards route, want to look into a way to integrate this with the regular submit check
			//this check seems to have some issue and is only currently being applied to the post filter data set.
			$projectWasSubmitted = $this->checkAllAssetsSubmitted($expenses);

            $responseArray['assets'] = $expenses;
            $responseArray['pages'] = $paginationResponse['pages'];
            $responseArray['projectDropDown'] = $allTheProjects;
            $responseArray['employeeDropDown'] = $employeeDropDown;
            $responseArray['showProjectDropDown'] = $showProjectDropDown;
            $responseArray['projectSubmitted'] = $projectWasSubmitted;

			$response->data = $responseArray;
			return $response;
		} catch(ForbiddenHttpException $e){
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
			PermissionsController::requirePermission('expenseGetAccountantDetails');
			
			//url decode filter value
            $filter = urldecode($filter);
			//explode by delimiter to allow for multi search
			$delimiter = ',';
			$filterArray = explode($delimiter, $filter);
				
			$expenseQuery = new Query;
			$expenseQuery->select('*')
                ->from(["fnGetExpensesByDate(:startDate, :endDate)"])
                ->addParams([':startDate' => $startDate, ':endDate' => $endDate])
				->where(['ProjectID' => $projectID]);

			//apply employee filter
			if($employeeID!= null){
                $expenseQuery->andWhere(['UserID' => $employeeID]);
            }
			
			if($filterArray!= null){
				//initialize array for filter query values
				$filterQueryArray = array('or');
				//loop for multi search
				for($i = 0; $i < count($filterArray); $i++){
					//remove leading space from filter string
					$trimmedFilter = trim($filterArray[$i]);
					array_push($filterQueryArray,
						['like', 'UserName', $trimmedFilter]
					);
				}
				$expenseQuery->andFilterWhere($filterQueryArray);
            }
	
			$expenses = $expenseQuery->all(BaseActiveRecord::getDb());
			
			//format response
			$responseArray['details'] = $expenses;
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
		} catch(ForbiddenHttpException $e) {
			throw $e;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionShowEntries($userID, $projectID, $startDate, $endDate){		
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();

			// RBAC permission check
			PermissionsController::requirePermission('expenseGetEntries');

			$dataArray = [];
			
			$entries = GetExpenses::find()
				->where(['and', 
					['UserID' => $userID],
					['ProjectID' => $projectID],
					['between', 'CreatedDate', $startDate, $endDate],
				])
				->orderBy('CreatedDate ASC')
				->all();
				
			$groupingQuery = new Query;
			$groupingQuery->select(['ProjectName', 'UserName', 'Quantity', 'IsApproved', 'IsSubmitted'])
				->from(["fnGetExpensesByDate(:startDate, :endDate)"])
				->addParams([':startDate' => $startDate, ':endDate' => $endDate])
				->where(['and', 
					['UserID' => $userID],
					['ProjectID' => $projectID],
				]);
			$grouping = $groupingQuery->one(BaseActiveRecord::getDb());
			
			$transaction->commit();

			$dataArray['entries'] = $entries;
			$dataArray['groupData'] = $grouping;

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $dataArray;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionGetModalDropdown($projectID = null){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			//format response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
				
			$projectArray = [''=>'Select'];
			$userArray = [''=>'Select'];
			if (PermissionsController::can('expenseGetAll')){
				//all projects that have user relations
				$projects = Project::find()
					->select(['ProjectID', "concat(ProjectName, '(' , ProjectReferenceID , ')') as ProjectName"])
					->innerJoin('Project_User_Tb', '[ProjectTb].[ProjectID] = [Project_User_Tb].[ProjUserProjectID]')
					->distinct()
					->all();
			}elseif(PermissionsController::can('expenseGetOwn')){
				//get requesting user
				$userID = BaseActiveController::getUserFromToken()->UserID;
				//get user project relations array
				$projects = Project::find()
					->select(['ProjectID', "concat(ProjectName, '(' , ProjectReferenceID ,')') as ProjectName"])
					->innerJoin('Project_User_Tb', '[ProjectTb].[ProjectID] = [Project_User_Tb].[ProjUserProjectID]')
					->where(['ProjUserUserID'=>$userID])
					->distinct()
					->all();
			}
			
			$relatedProjectIDs = [];
			//get project ids or user query
			foreach ($projects as $p){
				$relatedProjectIDs[] = $p->ProjectID;
			}
			
			$users = BaseUser::find()
				->select(['UserID', "concat(UserFirstName , ', ' , UserLastName , '(' , UserName , ')') as UserName"])
				->innerJoin('Project_User_Tb', '[UserTb].[UserID] = [Project_User_Tb].[ProjUserUserID]');
				
			if($projectID != null){
				$users->where(['ProjUserProjectID' => $projectID]);
			}else{
				$users->where(['in', 'ProjUserProjectID' , $relatedProjectIDs]);
			}
				
			$users = $users->distinct()
				->andWhere(['<>','UserAppRoleType', 'Admin'])
				->all();
			
			$projectArray = self::extractProjects($projects, $projectArray);
			$userArray = self::extractEmployees($users, $userArray);
			
			$responseArray['projectDropdown'] = $projectArray;
			$responseArray['employeeDropdown'] = $userArray;
			//hardcoded coa for now
			$responseArray['coaDropdown'] = [4450 => 'Per Deim'];
			$response->data = $responseArray;
			$response->setStatusCode(200);
			return $response;
		}catch(ForbiddenHttpException $e) {
			throw $e;
		}catch(\Exception $e){
		   throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionCreate(){
		try{
			//set client header
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('expenseCreate');
			
			//get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
	
			//try catch to log expense object error
			try{					
				$successFlag = 0;
				$expense = new Expense;
				$expense->attributes = $data;
				//get username based off id given
				$user = BaseUser::find()
					->select('UserName')
					->where(['UserID' => $data['UserID']])
					->one();
				$username = $user->UserName;
				$expense->Username = $username;
				$expense->CreatedDate = $data['CreatedDateTime'];

				if ($expense->save()){
					$successFlag = 1;
				} else {
					throw BaseActiveController::modelValidationException($expense);
				}
			}catch(yii\db\Exception $e){
				//if db exception is 2601, duplicate contraint then success
				if(in_array($e->errorInfo[1], array(2601, 2627))){
					$successFlag = 1;
				}else{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
					$successFlag = 0;
				}
			}
			$responseData = [
				'SuccessFlag' => $successFlag
			];
			//return response data
			return $responseData;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
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

				//build base query
				$checkQuery = new Query;
				$checkQuery->select('*')
					->from(["fnExpenseSubmitCheck(:StartDate , :EndDate)"])
					->addParams([
						':StartDate' => $submitCheckData['StartDate'], 
						':EndDate' => $submitCheckData['EndDate']]);
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
	
	/**
	*Call sp to process expense data and generate account file
	*Looks for JSON PUT body containing date range and project IDs to process
	*@RETURNS JSON w/success flag and comment
	*/
	public function actionAccountantSubmit(){
		try{
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
			$outputData = self::getSubmissionFileData($params);
			if($outputData === false){
				$comments = 'Failed to get Output Data.';
				//self::resetSubmission($params, $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			
			//call function to write files, check after each file for failure
			$writeStatus = count($outputData) != 0 ? self::writeFileData($outputData, Constants::EXPENSE_OUTPUT) : true;
			if(!$writeStatus){
				$comments = 'Failed to write expense file.';
				//self::resetSubmission($params, $comments);
				$responseData['comments'] = $comments;
				$response->data = $responseData;
				return $response;
			}
			
			//if all process run successfully return success
			$responseData['success'] = 1;
			$responseData['comments'] = 'Expense submission processed successfully.';
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
	
	private static function getSubmissionFileData($params){
		try{
			$projectIDs = $params['params']['projectIDArray'];
			$startDate = $params['params']['startDate'];
			$endDate = $params['params']['endDate'];
			$createdBy = BaseActiveController::getUserFromToken()->UserName;
			$spName = 'spExpenseSubmission';
			$eventHistoryType = Constants::EXPENSE_SUBMISSION;
			
			//submit files and get output data
			$db = BaseActiveRecord::getDb();
			$getFileDataCommand = $db->createCommand("SET NOCOUNT ON EXECUTE $spName :projectIDs, :startDate, :endDate, :createdBy");
			$getFileDataCommand->bindParam(':projectIDs', $projectIDs, \PDO::PARAM_STR);
			$getFileDataCommand->bindParam(':startDate', $startDate, \PDO::PARAM_STR);
			$getFileDataCommand->bindParam(':endDate', $endDate, \PDO::PARAM_STR);
			$getFileDataCommand->bindParam(':createdBy', $createdBy, \PDO::PARAM_STR);
			$fileData = $getFileDataCommand->query();
			
			//log submission
			self::logExpenseHistory(Constants::EXPENSE_SUBMISSION, null, $startDate, $endDate);
			
			return $fileData;
		} catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'getSubmissionFileData',
				$e,
				getallheaders()['X-Client'],
				'Params: ' . json_encode($params),
				'Type: ' . Constants::EXPENSE_OUTPUT
			);
			return false;
		}
	}
	
	private static function writeFileData($data){
		try{
			$fileNamePrefix = Constants::EXPENSE_FILE_NAME;
			//get date and format for file name
			$date = BaseActiveController::getDate();
			$formatedDate = str_replace([' ', ':'], '_', $date);
			$fileName = $fileNamePrefix . $formatedDate;
			
			//data is the sp response for the given file, file name payroll_history_2018-03-27_9_36_36.csv, type is type of file being written
			BaseActiveController::processAndWriteCsv($data,$fileName,Constants::EXPENSE_OUTPUT);
			return true;
		}catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'writeFileData',
				$e,
				getallheaders()['X-Client'],
				'Data: ' . json_encode($data),
				'Type: ' . Constants::EXPENSE_OUTPUT
			);
			return false;
		}
	}
	
	private function extractProjects($dropdownRecords, $projectAllOption){
		$allTheProjects = [];
		//iterate and stash project name $p['ProjectID']
		foreach ($dropdownRecords as $p) {		
			$key = $p['ProjectID'];
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
	
	private function extractEmployees($dropdownRecords, $employeeAllOption = null){
		$employeeValues = [];
		//iterate and stash user values
		foreach ($dropdownRecords as $e) {
			//build key value pair
			$key = $e['UserID'];
			$value = $e['UserName'];
			$employeeValues[$key] = $value;
		}
		//remove dupes
		$employeeValues = array_unique($employeeValues);
		//abc order for all
		asort($employeeValues);
		//append all option to the front
		$employeeAllOption = $employeeAllOption == null ? [""=>"All"] : $employeeAllOption;
		$employeeValues = $employeeAllOption + $employeeValues;
		
		return $employeeValues;
	}
	
	/**
    * Check if there is at least one card to be approved
    * @param $expesnseArr
    * @return boolean
    */
    private function checkUnapprovedExist($expesnseArr){
        foreach ($expesnseArr as $item){
            if ($item['IsApproved'] == 0){
                return true;
            }
        }
        return false;
    }
	
	/**
    * Check if project was submitted
    * @param $expesnseArray
    * @return boolean
    */
    private function checkAllAssetsSubmitted($expesnseArray){
        foreach ($expesnseArray as $item)
		{
			if ($item['IsSubmitted'] == 0){
				return false;
			}
        }
        return true;
    }
	
	private function logExpenseHistory($type, $expenseID = null, $startDate = null, $endDate = null, $comments = null){
		try{
			//create and populate model
			$historyRecord = new ExpenseEventHistory;
			$historyRecord->Date = BaseActiveController::getDate();
			$historyRecord->Name = BaseActiveController::getUserFromToken()->UserName;
			$historyRecord->Type = $type;
			$historyRecord->ExpenseID = $expenseID;
			$historyRecord->StartDate = $startDate;
			$historyRecord->EndDate = $endDate;
			$historyRecord->Comments = $comments;

			//save
			if(!$historyRecord->save()){
				//throw error on failure
				throw BaseActiveController::modelValidationException($newInspection);
			}
		}catch(\Exception $e){
			//catch and log errors
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
		}
	}
	
	//helper function 
	//params expense model, username of modifying user, and type of change being performed
	//returns true if successful
	private function createHistoryRecord($expense, $modifiedBy, $modifiedDate, $changeType){
		//new history record
		$historyModel = new ExpenseEntryEventHistory;
		$historyModel->Attributes = $expense->attributes;
		$historyModel->ExpenseID = $expense->ID;
		$historyModel->ChangeMadeBy = $modifiedBy;
		$historyModel->ChangeDateTime = $modifiedDate;
		$historyModel->Change = $changeType;
		if($historyModel->save()){
			return true;
		}
		return false;
	}
}