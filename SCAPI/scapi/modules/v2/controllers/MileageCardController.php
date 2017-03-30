<?php

namespace app\modules\v2\controllers;
/*
 * NOTICE: If you want to replace any of the below v1 classes with a v2 version
 * you must change the corresponding use statement.
 */
use app\modules\v1\controllers\CreateMethodNotAllowed;
use app\modules\v1\controllers\DeleteMethodNotAllowed;
use app\modules\v1\controllers\PermissionsController;
use app\modules\v1\controllers\UpdateMethodNotAllowed;
use Yii;
use app\modules\v1\models\MileageCard;
use app\modules\v1\models\MileageEntry;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\Project;
use app\modules\v1\models\ProjectUser;
use app\modules\v1\models\AllMileageCardsCurrentWeek;
use app\modules\v1\models\MileageCardSumMilesCurrentWeekWithProjectNameNew;
use app\modules\v1\models\MileageCardSumMilesPriorWeekWithProjectNameNew;
use app\modules\v1\controllers\BaseActiveController;
use app\authentication\TokenAuth;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\debug\components\search\matchers\Base;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use \DateTime;
use yii\data\Pagination;

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
			$approvedBy = self::getUserFromToken()->UserID;

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
					//$card-> MileageCardModifiedBy = $approvedBy;
					$card-> update();
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$data = [];
				$data['cards'] = $approvedCards;
				$data['success'] = true;
				$response->data = $data;
				return $response;
			}
			//if transaction fails rollback changes and send error
			catch(Exception $e)
			{
				$transaction->rollBack();
				$response->setStatusCode(400);
                $data = [];
                $data['cards'] = null;
                $data['status'] = "400 Bad Request";
                $data['success'] = false;
				$response->data = $data;
				return $response;
			}
		}
		catch(\Exception $e) 
		{
		    throw $e;
            $response->setStatusCode(400);
            $data = [];
            $data['cards'] = null;
            $data['status'] = "400 Bad Request";
            $data['success'] = false;
            $response->data = $data;
            return $response;
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
			$date = new DateTime($mileageCard-> MileageStartDate);
			
			//get all time entries for Sunday
			$sundayDate = $date;
			$sundayStr = $sundayDate->format(BaseActiveController::DATE_FORMAT);
			$sundayEntries = MileageEntry::find()
				->where("MileageEntryDate ="."'"."$sundayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
			
			//get all time entries for Monday
			$mondayDate = $date->modify('+1 day');	
			$mondayStr = $mondayDate->format(BaseActiveController::DATE_FORMAT);
			$mondayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$mondayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Tuesday	
			$tuesdayDate = $date->modify('+1 day');
			$tuesdayStr = $tuesdayDate->format(BaseActiveController::DATE_FORMAT);
			$tuesdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$tuesdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Wednesday	
			$wednesdayDate = $date->modify('+1 day');
			$wednesdayStr = $wednesdayDate->format(BaseActiveController::DATE_FORMAT);
			$wednesdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$wednesdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Thursday
			$thursdayDate = $date->modify('+1 day');
			$thursdayStr = $thursdayDate->format(BaseActiveController::DATE_FORMAT);
			$thursdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$thursdayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Friday
			$fridayDate = $date->modify('+1 day');
			$fridayStr = $fridayDate->format(BaseActiveController::DATE_FORMAT);
			$fridayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$fridayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//get all time entries for Saturday
			$satudayDate = $date->modify('1 day');
			$satudayStr = $satudayDate->format(BaseActiveController::DATE_FORMAT);
			$saturdayEntries =MileageEntry::find()
				->where("MileageEntryDate ="."'"."$satudayStr". "'")
				->andWhere("MileageEntryMileageCardID = $cardID")
				->all();
				
			//load data into array
			$dataArray["StartDate"] = $mileageCard-> MileageStartDate;
			$dataArray["EndDate"] = $mileageCard-> MileageEndDate;
			$dataArray["ApprovedFlag"] = $mileageCard-> MileageCardApprovedFlag;
			$dayArray =
			[
				"Sunday" => $sundayEntries,
				"Monday" => $mondayEntries,
				"Tuesday" => $tuesdayEntries,
				"Wednesday" => $wednesdayEntries,
				"Thursday" => $thursdayEntries,
				"Friday" => $fridayEntries,
				"Saturday" => $saturdayEntries,
			];
			$dataArray["MileageEntries"] = [$dayArray];
			
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $dataArray;
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
	
	public function actionGetCards($week, $listPerPage = null, $page = null)
	{
		// RBAC permission check is embedded in this action	
		try
		{
			//set db target headers
			MileageCardSumMilesCurrentWeekWithProjectNameNew::setClient(BaseActiveController::urlPrefix());
			
			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;
			
			//response array of mileage cards
			$mileageCardArray = [];
            $mileageCardsArr = [];
            $responseArray = [];
			
			//rbac permission check
			if (PermissionsController::can('mileageCardGetAllCards'))
			{
				//check if week is prior or current to determine appropriate view
				if($week == 'prior')
				{
                    $mileageCards = MileageCardSumMilesPriorWeekWithProjectNameNew::find();
                    $paginationResponse = self::paginationProcessor($mileageCards, $page, $listPerPage);
                    $mileageCardsArr = $paginationResponse['Query']->orderBy('UserID,MileageStartDate,ProjectID')->all();
                    $responseArray['assets'] = $mileageCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
				} 
				elseif($week == 'current') 
				{
					$mileageCards = MileageCardSumMilesCurrentWeekWithProjectNameNew::find();
                    $paginationResponse = self::paginationProcessor($mileageCards, $page, $listPerPage);
                    $mileageCardsArr = $paginationResponse['Query']->orderBy('UserID,MileageStartDate,ProjectID')->all();
                    $responseArray['assets'] = $mileageCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
				}
			} 
			//rbac permission check
			elseif(PermissionsController::can('mileageCardGetOwnCards'))		
			{
				$userID = self::getUserFromToken()->UserID;
				//get user project relations array
				$projects = ProjectUser::find()
					->where("ProjUserUserID = $userID")
					->all();
				$projectsSize = count($projects);
				
				//check if week is prior or current to determine appropriate view
				if($week == 'prior' && $projectsSize > 0)
				{
                    $mileageCards = MileageCardSumMilesPriorWeekWithProjectNameNew::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
					for($i=0; $i < $projectsSize; $i++)
					{
						$projectID = $projects[$i]->ProjUserProjectID;
                        $mileageCards->andWhere(['ProjectID'=>$projectID]);
                        //$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
					}
                    $paginationResponse = self::paginationProcessor($mileageCards, $page, $listPerPage);
                    $mileageCardsArr = $paginationResponse['Query']->orderBy('UserID,MileageStartDate,ProjectID')->all();
                    $responseArray['assets'] = $mileageCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
				} 
				elseif($week == 'current' && $projectsSize > 0)
				{
                    $mileageCards = MileageCardSumMilesCurrentWeekWithProjectNameNew::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
					for($i=0; $i < $projectsSize; $i++)
					{
						$projectID = $projects[$i]->ProjUserProjectID;
                        $mileageCards->andWhere(['ProjectID'=>$projectID]);
						//$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
					}
                    $paginationResponse = self::paginationProcessor($mileageCards, $page, $listPerPage);
                    $mileageCardsArr = $paginationResponse['Query']->orderBy('UserID,MileageStartDate,ProjectID')->all();
                    $responseArray['assets'] = $mileageCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
				}
			}
			else{
				throw new ForbiddenHttpException;
			}
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
    public function paginationProcessor($assetQuery, $page, $listPerPage){

        if($page != null)
        {
            // set pagination
            $countAssetQuery = clone $assetQuery;
            $pages = new Pagination(['totalCount' => $countAssetQuery->count()]);
            $pages->pageSizeLimit = [1,100];
            $offset = $listPerPage*($page-1);
            $pages->setPageSize($listPerPage);
            $pages->pageParam = 'mileageCardPage';
            $pages->params = ['per-page' => $listPerPage, 'mileageCardPage' => $page];

            $assetQuery->offset($offset)
                ->limit($listPerPage);

            $asset['pages'] = $pages;
            $asset['Query'] = $assetQuery;

            return $asset;
        }
    }
}