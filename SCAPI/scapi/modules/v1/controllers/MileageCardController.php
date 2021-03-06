<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\MileageCard;
use app\modules\v1\models\MileageEntry;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\Project;
use app\modules\v1\models\ProjectUser;
use app\modules\v1\models\AllMileageCardsCurrentWeek;
use app\modules\v1\models\MileageCardSumMilesCurrentWeekWithProjectName;
use app\modules\v1\models\MileageCardSumMilesPriorWeekWithProjectName;
use app\modules\v1\controllers\BaseActiveController;
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

/**
 * MileageCardController implements the CRUD actions for MileageCard model.
 */
class MileageCardController extends BaseActiveController
{
    public $modelClass = 'app\modules\v1\models\MileageCard'; 
	
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
			foreach($cardIDs as $id)
			{
				$approvedCards[]= MileageCard::findOne($id);
			}
			
			//try to approve time cards
			try
			{
				//create transaction
				//$connection = \Yii::$app->db;
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
			$date = new DateTime($mileageCard-> MileageStartDate);
			
			$dayArray =
			[
				'Sunday' => [],
				'Monday' => [],
				'Tuesday' => [],
				'Wednesday' => [],
				'Thursday' => [],
				'Friday' => [],
				'Saturday' => [],
			];
			
			foreach ($dayArray as $day => $entries)
			{
				$dayStart = $date->format(BaseActiveController::DATE_FORMAT);
				$dayEnd = $date->modify('+1 day')->format(BaseActiveController::DATE_FORMAT);
				$dayArray[$day] = MileageEntry::find()
				->where([ 'and',
					['>=', 'MileageEntryDate', $dayStart],
					['<', 'MileageEntryDate', $dayEnd],
					['MileageEntryMileageCardID' => $cardID]
					])
				->all();
			}	
				
			//load data into array
			$dataArray['StartDate'] = $mileageCard-> MileageStartDate;
			$dataArray['EndDate'] = $mileageCard-> MileageEndDate;
			$dataArray['ApprovedFlag'] = $mileageCard-> MileageCardApprovedFlag;
			$dataArray['MileageEntries'] = [$dayArray];
			
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
	
	public function actionGetCards($week, $listPerPage = 10, $page = 1)
	{
		//RBAC permission check is embedded in this action	
		try
		{
			//set db target headers
			MileageCardSumMilesCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());
			
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
                    $mileageCards = MileageCardSumMilesPriorWeekWithProjectName::find();
                    $paginationResponse = self::paginationProcessor($mileageCards, $page, $listPerPage);
                    $mileageCardsArr = $paginationResponse['Query']->orderBy('UserID,MileageStartDate,ProjectID')->all();
                    $responseArray['assets'] = $mileageCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
				} 
				elseif($week == 'current') 
				{
					$mileageCards = MileageCardSumMilesCurrentWeekWithProjectName::find();
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
                    $mileageCards = MileageCardSumMilesPriorWeekWithProjectName::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
					if($projectsSize > 1)
					{
						for($i=1; $i < $projectsSize; $i++)
						{
							$projectID = $projects[$i]->ProjUserProjectID;
							$mileageCards->orWhere(['ProjectID'=>$projectID]);
						}
					}
                    $paginationResponse = self::paginationProcessor($mileageCards, $page, $listPerPage);
                    $mileageCardsArr = $paginationResponse['Query']->orderBy('UserID,MileageStartDate,ProjectID')->all();
                    $responseArray['assets'] = $mileageCardsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];
				} 
				elseif($week == 'current' && $projectsSize > 0)
				{
                    $mileageCards = MileageCardSumMilesCurrentWeekWithProjectName::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
					if($projectsSize > 1)
					{
						for($i=1; $i < $projectsSize; $i++)
						{
							$projectID = $projects[$i]->ProjUserProjectID;
							$mileageCards->orWhere(['ProjectID'=>$projectID]);
						}
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
		} catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}

    public function actionGetMileageCardsHistoryData($week)
    {
        // RBAC permission check is embedded in this action
        try
        {
            //set db target headers
            MileageCardSumMilesCurrentWeekWithProjectName::setClient(BaseActiveController::urlPrefix());

            //format response
            $response = Yii::$app->response;
            $response-> format = Response::FORMAT_JSON;

            //response array of mileage cards
            $mileageCardArray = [];
            $mileageCardsArr = [];

            //rbac permission check
            if (PermissionsController::can('mileageCardGetAllCards'))
            {
                //check if week is prior or current to determine appropriate view
                if($week == 'prior')
                {
                    $responseArray = MileageCardSumMilesPriorWeekWithProjectName::find()->orderBy('UserID,MileageStartDate,ProjectID')->createCommand();//->all();
                    $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                }
                elseif($week == 'current')
                {
                    $responseArray = MileageCardSumMilesCurrentWeekWithProjectName::find()->orderBy('UserID,MileageStartDate,ProjectID')->createCommand();//->all();
                    $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
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
                    $mileageCards = MileageCardSumMilesPriorWeekWithProjectName::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
                    for($i=0; $i < $projectsSize; $i++)
                    {
                        $projectID = $projects[$i]->ProjUserProjectID;
                        $mileageCards->andWhere(['ProjectID'=>$projectID]);
                        //$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
                    }
                    $responseArray = $mileageCards->orderBy('UserID,MileageStartDate,ProjectID')->createCommand();//->all();
                    $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                }
                elseif($week == 'current' && $projectsSize > 0)
                {
                    $mileageCards = MileageCardSumMilesCurrentWeekWithProjectName::find()->where(['ProjectID' => $projects[0]->ProjUserProjectID]);
                    for($i=0; $i < $projectsSize; $i++)
                    {
                        $projectID = $projects[$i]->ProjUserProjectID;
                        $mileageCards->andWhere(['ProjectID'=>$projectID]);
                        //$mileageCardArray = array_merge($mileageCardArray, $mileageCards);
                    }
                    $responseArray = $mileageCards->orderBy('UserID,MileageStartDate,ProjectID')->createCommand();//->all();
                    $responseArray = $responseArray->query(); // creates a reader so that information can be processed one row at a time
                }
            }
            else{
                throw new ForbiddenHttpException;
            }

            if (!empty($responseArray))
            {
                $this->processAndOutputCsvResponse($responseArray);
                return '';
            }
            $this->setCsvHeaders();
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
