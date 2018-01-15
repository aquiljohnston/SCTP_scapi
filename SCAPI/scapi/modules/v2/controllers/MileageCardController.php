<?php

namespace app\modules\v2\controllers;

use app\modules\v2\controllers\CreateMethodNotAllowed;
use app\modules\v2\controllers\DeleteMethodNotAllowed;
use app\modules\v2\controllers\PermissionsController;
use app\modules\v2\controllers\UpdateMethodNotAllowed;
use Yii;
use app\modules\v2\models\MileageCard;
use app\modules\v2\models\MileageEntry;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\Project;
use app\modules\v2\models\ProjectUser;
use app\modules\v2\models\AllMileageCardsCurrentWeek;
use app\modules\v2\models\MileageCardSumMilesCurrentWeekWithProjectName;
use app\modules\v2\models\MileageCardSumMilesPriorWeekWithProjectName;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\controllers\BaseActiveController;
use app\authentication\TokenAuth;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\debug\components\search\matchers\Base;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use \DateTime;
use yii\data\Pagination;
use yii\db\query;

/**
 * MileageCardController implements the CRUD actions for MileageCard model.
 */
class MileageCardController extends BaseActiveController
{
    public $modelClass = 'app\modules\v2\models\MileageCard'; 
	
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
     * Displays a single MileageCard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		try
		{			
			//set db target
			$headers = getallheaders();
			MileageCard::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageCardView');
			
			$mileageCard = MileageCard::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $mileageCard;
			
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
	
	public function actionGetEntries($cardID)
	{		
		try
		{
			//set db target
			MileageCard::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageCardGetEntries');
			
			$response = Yii::$app ->response;
			$dataArray = [];
			$mileageCard = MileageCard::findOne($cardID);
			
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
					->from("fnMileageCardEntrysByMileageCard(:cardID)")
					->addParams([':cardID' => $cardID])
					->orderBy('MileageEntryStartTime');
			$entries = $entriesQuery->all(BaseActiveRecord::getDb());
			
			foreach ($entries as $entry)
			{
				$dayArray[$entry['MileageEntryWeekDay']]['Entries'][] = $entry;
			}
			foreach ($dayArray as $day => $data)
			{
				if(count($dayArray[$day]['Entries']) > 0)
				{
					$dayArray[$day]['Total'] = $data['Entries'][0]['DayTotalMiles'];
				}
			}
			
			//load data into array
			$dataArray['StartDate'] = $mileageCard-> MileageStartDate;
			$dataArray['EndDate'] = $mileageCard-> MileageEndDate;
			$dataArray['ApprovedFlag'] = $mileageCard-> MileageCardApprovedFlag;
			$dataArray['MileageEntries'] = $dayArray;
			
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $dataArray;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	//is this route even used? seems like it would not work with multiple project structure.
	//appears time card version of this is still in use, this will probably need to be altered that new structure
	public function actionGetCard($userID)
	{		
		try
		{
			//set db target
			AllMileageCardsCurrentWeek::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageCardGetCard');
			
			$mileageCard = AllMileageCardsCurrentWeek::findOne(['UserID'=>$userID]);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			if ($mileageCard != null)
			{
				$response->setStatusCode(200);
				$response->data = $mileageCard;
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
		// RBAC permission check is embedded in this action	
		try
		{
			//get headers
			$headers = getallheaders();
			//get client header
			$client = $headers['X-Client'];
			
			//url decode filter value
			$filter = urldecode($filter);
			
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//response array of mileage cards
            $mileageCardsArr = [];
            $responseArray = [];
			
			//build base query
			$mileageCards = new Query;
			$mileageCards->select('*')
				->from(["fnAllMileageCards(:startDate, :endDate)"])
				->addParams([':startDate' => $startDate, ':endDate' => $endDate]);
			
			//if is scct website get all or own
			if(BaseActiveController::isSCCT($client))
			{
				//rbac permission check
				if(!PermissionsController::can('mileageCardGetAllCards') &&PermissionsController::can('mileageCardGetOwnCards'))		
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
            if($filter != null && isset($mileageCards))
            {
                $mileageCards->andFilterWhere([
                    'or',
                    //['like', 'UserName', $filter],
                    ['like', 'UserFirstName', $filter],
                    ['like', 'UserLastName', $filter],
                    ['like', 'ProjectName', $filter],
                    ['like', 'MileageCardApproved', $filter]
                ]);
            }
            $paginationResponse = BaseActiveController::paginationProcessor($mileageCards, $page, $listPerPage);
            $mileageCardsArr = $paginationResponse['Query']->orderBy('UserID,MileageCardProjectID')->all(BaseActiveRecord::getDb());
            $responseArray['assets'] = $mileageCardsArr;
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
		} catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
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
                $this->processAndOutputCsvResponse($responseArray);
                return '';
            }
            $this->setCsvHeaders();
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
                fputcsv($fp, array_keys($row));
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
}
