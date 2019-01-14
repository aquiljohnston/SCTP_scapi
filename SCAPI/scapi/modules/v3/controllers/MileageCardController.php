<?php

namespace app\modules\v3\controllers;

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
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\controllers\BaseActiveController;
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
class MileageCardController extends BaseActiveController
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
					'view' => ['get'],
					'approve-cards'  => ['put'],
					'get-card' => ['get'],
					'get-cards' => ['get'],
					'show-entries' => ['get'],
					'get-cards-export' => ['get'],
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

			//parse json
			$cardIDs = $data["cardIDArray"];
			
			//get timecards
			$approvedCards = []; //Prevent uninitialized error
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
					$card-> MileageCardApprovedFlag = "Yes";
					$card-> MileageCardApprovedBy = $approvedBy;
					$card-> MileageCardModifiedDate = Parent::getDate();
					$card-> update();
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
	
	public function actionShowEntries($cardID)
	{		
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
			$showProjectDropdown = false;
			
			//build base query
			$mileageCards = new Query;
			$mileageCards->select('*')
				->from(["fnMileageCardByDate(:startDate, :endDate)"])
				->addParams([':startDate' => $startDate, ':endDate' => $endDate]);
			
			//if is scct website get all or own
			if(BaseActiveController::isSCCT($client))
			{
				//set project dropdown to true for scct
				$showProjectDropDown = true;
				//rbac permission check
				if(!PermissionsController::can('mileageCardGetAllCards') &&PermissionsController::can('mileageCardGetOwnCards'))		
				{
					$userID = self::getUserFromToken()->UserID;
					//get user project relations array
					$projects = ProjectUser::find()
						->where("ProjUserUserID = $userID")
						->all();
					$projectsSize = count($projects);
					if($projectsSize > 0)
					{
						$mileageCards->where(['MileageCardProjectID' => $projects[0]->ProjUserProjectID]);
					} else {
						//can only get own but has no project relations
						throw new ForbiddenHttpException;
					}
                    if($projectsSize > 1)
                    {
						//add all option to project dropdown if there will be more than one option
						$projectAllOption = [""=>"All"];
                        for($i=1; $i < $projectsSize; $i++)
                        {
                            $projectID = $projects[$i]->ProjUserProjectID;
                            $mileageCards->orWhere(['MileageCardProjectID'=>$projectID]);
                        }
                    }
				}
			}
			else // get only cards for the current project.
			{
				//get project based on client header
				$project = Project::find()
					->where(['ProjectUrlPrefix' => $client])
					->one();
				//add project where to query
				$mileageCards->where(['MileageCardProjectID' => $project->ProjectID]);
			}
			
			//get records post user/permissions filter for project dropdown(timing for this execution is very important)
			$projectDropdownRecords = $mileageCards->all(BaseActiveRecord::getDb());

			//apply project filter
            if($projectID!= null && isset($mileageCards)) {
                $mileageCards->andFilterWhere([
                    'and',
                    ['MileageCardProjectID' => $projectID],
                ]);
            }

			//get records post user/permissions/project filter for employee dropdown(timing for this execution is very important)
			$employeeDropdownRecords = $mileageCards->all(BaseActiveRecord::getDb());
			
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
				for($i = 0; $i < count($filterArray); $i++)
				{
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
			$projectDropDown = self::extractProjectsFromMileageCards($projectDropdownRecords, $projectAllOption);
			
			//get employee list for dropdown based on time cards available
			$employeeDropDown = self::extractEmployeesFromMileageCards($employeeDropdownRecords);
			
			//add pagination and fetch mileage card data
            $paginationResponse = BaseActiveController::paginationProcessor($mileageCards, $page, $listPerPage);
            $mileageCardsArr = $paginationResponse['Query']->orderBy("$sortField $sortOrder")->all(BaseActiveRecord::getDb());
			
			//check mileage card submission statuses
            $unapprovedMileageCardExist = $this->CheckUnapprovedMileageCardExist($mileageCardsArr);
			//unsure of business rules for mileage submission
            $projectWasSubmitted = $this->CheckAllAssetsSubmitted($mileageCardsArr);
			
			$transaction->commit();
			
            $responseArray['assets'] = $mileageCardsArr;
            $responseArray['pages'] = $paginationResponse['pages'];
			$responseArray['projectDropDown'] = $projectDropDown;
            $responseArray['employeeDropDown'] = $employeeDropDown;
            $responseArray['showProjectDropDown'] = $showProjectDropDown;
			$responseArray['unapprovedMileageCardExist'] = $unapprovedMileageCardExist;
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

    public function actionGetCardsExport($startDate, $endDate)
    {
        // RBAC permission check is embedded in this action
        try
        {
			//get headers
			$headers = getallheaders();
			//get client header
			$client = $headers['X-Client'];
			
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;

            //response array of mileage cards
            $mileageCardArray = [];
            $mileageCardsArr = [];
			
			$mileageCards = new Query;
			$mileageCards->select('*')
				->from(["fnAllMileageCards(:startDate, :endDate)"])
				->addParams([':startDate' => $startDate, ':endDate' => $endDate]);

			if(BaseActiveController::isSCCT($client))
			{
				//rbac permission check
				if(!PermissionsController::can('mileageCardGetAllCards') && PermissionsController::can('mileageCardGetOwnCards'))
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
						$mileageCards->where(['MileageCardProjectID' => $projects[0]->ProjUserProjectID]);
					}
					if($projectsSize > 1)
					{
						for($i=1; $i < $projectsSize; $i++)
						{
							$projectID = $projects[$i]->ProjUserProjectID;
							$mileageCards->orWhere(['MileageCardProjectID'=>$projectID]);
						}
					}
				}
				elseif(!PermissionsController::can('mileageCardGetAllCards')){
					throw new ForbiddenHttpException;
				}
			} else
			{
				//get project based on client header
				$project = Project::find()
					->where(['ProjectUrlPrefix' => $client])
					->one();
				//add project where to query
				$mileageCards->where(['MileageCardProjectID' => $project->ProjectID]);
			}

			// creates a reader so that information can be processed one row at a time
			$responseArray = $mileageCards->orderBy('UserID,MileageCardProjectID')
				->createCommand(BaseActiveRecord::getDb())
				->query();
			
            if (!empty($responseArray))
            {
                self::processAndOutputCsvResponse($responseArray);
                return '';
            }
            self::setCsvHeaders();
            //send response
            return '';
        } catch(ForbiddenHttpException $e) {
            //Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            //Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

	//TODO consider creating cards parent controller and extracting out these 4 helper methods to combine with time cards
	//extractProjectsFromMileageCards, extractEmployeesFromMileageCards, CheckUnapprovedMileageCardExist, CheckAllAssetsSubmitted
	private function extractProjectsFromMileageCards($dropdownRecords, $projectAllOption)
	{
		$allTheProjects = [];
		//iterate and stash project name
		foreach ($dropdownRecords as $p) {
			//second option is only needed for the accountant view in timecard because the tables dont match
			//currently only two option exist for key would have to update this if more views/tables/functions use this function
			//$key = array_key_exists('MileageCardProjectID', $p) ? $p['MileageCardProjectID'] : $p['ProjectID'];
			$key = $p['MileageCardProjectID'];
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
	
	private function extractEmployeesFromMileageCards($dropdownRecords)
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
	
	/**
     * Check if there is at least one time card to be been approved
     * @param $mileageCardsArr
     * @return boolean
     */
    private function CheckUnapprovedMileageCardExist($mileageCardsArr){
        foreach ($mileageCardsArr as $item){
            if ($item['MileageCardApprovedFlag'] == 0){
                return true;
            }
        }
        return false;
    }
	
	/**
     * Check if project was submitted to Oasis and QB
     * @param $mileageCardsArr
     * @return boolean
     */
    private function CheckAllAssetsSubmitted($mileageCardsArr){
        foreach ($mileageCardsArr as $item)
		{
			//second option is only needed for the accountant view in timecard because the tables dont match
			// $oasisKey = array_key_exists('MileageCardOasisSubmitted', $item) ? 'MileageCardOasisSubmitted' : 'OasisSubmitted';
			// $qbKey = array_key_exists('MileageCardQBSubmitted', $item) ? 'MileageCardQBSubmitted' : 'QBSubmitted';
			$oasisKey = 'MileageCardOasisSubmitted';
			$qbKey = 'MileageCardQBSubmitted';
			
            if ($item[$oasisKey] == "No" || $item[$qbKey] == "No" ){
                return false;
            }
        }
        return true;        
    }
	
	//TODO change to use base active controller version when updates are completed to match time cards
    // helper method for setting the csv header for tracker maps csv output
    public static function setCsvHeaders(){
        header('Content-Type: text/csv;charset=UTF-8');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

	//TODO change to use base active controller version when updates are completed to match time cards
    // helper method for outputting csv data without storing the whole result
    public static function processAndOutputCsvResponse($reader){
        Yii::$app->response->format = Response::FORMAT_RAW;

        self::setCsvHeaders();
        // TODO find a way to use Yii response but without storing the whole response content in a variable
        $firstLine = true;
        $fp = fopen('php://output','w');

        while($row = $reader->read()){
            if($firstLine) {
                $firstLine = false;
                fputcsv($fp, array_keys($row));
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
}
