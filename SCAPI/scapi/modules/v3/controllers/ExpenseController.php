<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\ProjectUser;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\Expense;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

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

				if ($expense->save()) {
					$successFlag = 1;
				} else {
					throw BaseActiveController::modelValidationException($expense);
				}
			}catch(\Exception $e){
				//if db exception is 2601, duplicate contraint then success
				//if(in_array($e->errorInfo[1], array(2601, 2627))){
				//	$successFlag = 1;
				//}else{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
				$successFlag = 0;
			}
			$responseData = [
				'CreatedDate' => $data['CreatedDate'],
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

            //build base query
            $expenses = Expense::find()
				->select([
					'ID',
					'Expense.ProjectID',
					'ProjectName',
					'UserID',
					'Expense.UserName',
					'ChargeAccount',
					'Quantity',
					'CreatedDate',
					'IsApproved',
					'IsSubmitted'
				])
				->innerJoin('ProjectTb', '[Expense].[ProjectID] = [ProjectTb].[ProjectID]')
				->innerJoin('UserTb', '[Expense].[UserName] = [UserTb].[UserName]')
                ->where(['between', '[Expense].CreatedDate', $startDate, $endDate]);

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
                        $expenses->where(['Expense.ProjectID' => $projects[0]->ProjUserProjectID]);
                    }else{
						//can only get own but has no project relations
						throw new ForbiddenHttpException;
					}if($projectsSize > 1){
						//add all option to project dropdown if there will be more than one option
						$projectAllOption = [""=>"All"];
                        for($i=1; $i < $projectsSize; $i++){
                            $relatedProjectID = $projects[$i]->ProjUserProjectID;
							//could be an 'IN' instead
                            $expenses->orWhere(['Expense.ProjectID'=>$relatedProjectID]);
                        }
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
                $expenses->where(['Expense.ProjectID' => $project->ProjectID]);
            }

			//get records post user/permissions filter for project dropdown(timing for this execution is very important)
			$preFilteredRecords = $expenses->asArray()->all(BaseActiveRecord::getDb());

			//apply project filter
            if($projectID!= null && isset($expenses)) {
                $expenses->andFilterWhere([
                    'and',
                    ['Expense.ProjectID' => $projectID],
                ]);
            }

			if($projectID == null){
				$projectFilteredRecords = $preFilteredRecords;
			}else{
				//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
				$projectFilteredRecords = $expenses->asArray()->all(BaseActiveRecord::getDb());
			}
			
			//apply employee filter
			if($employeeID!= null && isset($expenses)) {
                $expenses->andFilterWhere([
                    'and',
                    ['UserID' => $employeeID],
                ]);
            }
			
			if($filterArray!= null && isset($expenses)) { //Empty strings or nulls will result in false
				//initialize array for filter query values
				$filterQueryArray = array('or');
				//loop for multi search
				for($i = 0; $i < count($filterArray); $i++){
					//remove leading space from filter string
					$trimmedFilter = trim($filterArray[$i]);
					array_push($filterQueryArray,
						['like', 'Expense.UserName', $trimmedFilter],
						['like', 'Quantity', $trimmedFilter],
						['like', 'ChargeAccount', $trimmedFilter],
						['like', 'CreatedDate', $trimmedFilter]
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
	
	private function extractProjects($dropdownRecords, $projectAllOption)
	{
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
	
	private function extractEmployees($dropdownRecords)
	{
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
		$employeeValues = [""=>"All"] + $employeeValues;
		
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
    protected function checkAllAssetsSubmitted($expesnseArray){
        foreach ($expesnseArray as $item)
		{
			if ($item['IsSubmitted'] == 0){
				return false;
			}
        }
        return true;
    }
}