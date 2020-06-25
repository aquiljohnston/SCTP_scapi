<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\constants\Constants;
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
use yii\db\Query;

/*
* Implements basic functions for mileage and time cards
*/
class BaseCardController extends BaseActiveController
{
	public function behaviors(){
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
                    'p-m-reset-request' => ['post'],
                ],
            ];
        return $behaviors;
    }
	
	public function actionPMResetRequest(){
		try{			
			$post = file_get_contents("php://input");
			$jsonArray = json_decode($post, true);
			
			//format response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			
			//set db target headers
          	BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//get user
			$username = self::getUserFromToken()->UserName;
			
			//archive json
			BaseActiveController::archiveWebJson($post, 'PM Reset Request', $username, BaseActiveController::urlPrefix());
			
			//set params based on reset type
			if($jsonArray['requestType'] == 'time-card'){
				$type = Constants::NOTIFICATION_TYPE_TIME;
				$description = Constants::NOTIFICATION_DESCRIPTION_RESET_REQUEST_PM_TIME;
				$cardIDName = 'TimeCardID';
			}elseif($jsonArray['requestType'] == 'mileage-card'){
				$type = Constants::NOTIFICATION_TYPE_MILEAGE;
				$description = Constants::NOTIFICATION_DESCRIPTION_RESET_REQUEST_PM_MILEAGE;
				$cardIDName = 'MileageCardID';
			}
			
			//fetch all time cards for selected projects
			$cardIDs = [];
			$startDate = $jsonArray['dateRangeArray'][0];
			$endDate = $jsonArray['dateRangeArray'][1];
			for($i = 0; $i < count($jsonArray['projectIDArray']); $i++){
				$projectID = $jsonArray['projectIDArray'][$i];
				$newCards = self::getCardsByProject($projectID, $startDate, $endDate, $type);
				$newCards = array_column($newCards, $cardIDName);
				$cardIDs = array_merge($cardIDs, $newCards);
			}
			//encode array to pass to sp
			$cardIDs = json_encode($cardIDs);
			
			//create new notification
			NotificationController::create(
				$type,
				$cardIDs,
				$description,
				Constants::APP_ROLE_ACCOUNTANT,
				$username);
			
			$status['success'] = true;
			$response->data = $status;	
			return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(
				'PM Reset Request',
				$e,
				getallheaders()['X-Client']
			);
			throw new \yii\web\HttpException(400);
		}
	}
	
	protected function extractClientFromCards($dropdownRecords, $allOption){
		$allClients = [];
		//iterate and stash client name $c['ClientID']
		foreach ($dropdownRecords as $c) {
			//currently only two option exist for key would have to update this if more views/tables/functions use this function
			//should look into standardizing this field			
			$key = $c['ClientID'];
			$value = $c['ClientName'];
			$allClients[$key] = $value;
		}
		//remove dupes
		$allClients = array_unique($allClients);
		//abc order for all
		natcasesort($allClients);
		//appened all option to the front
		$allClients = $allOption + $allClients;
		
		return $allClients;
	}
	
	protected function extractProjectsFromCards($type, $dropdownRecords, $allOption){
		$allTheProjects = [];
		//iterate and stash project name $p['ProjectID']
		foreach ($dropdownRecords as $p) {
			//currently only two option exist for key would have to update this if more views/tables/functions use this function
			//should look into standardizing this field			
			$key = array_key_exists($type.'ProjectID', $p) ? $p[$type.'ProjectID'] : $p['ProjectID'];
			$value = $p['ProjectName'];
			$allTheProjects[$key] = $value;
		}
		//remove dupes
		$allTheProjects = array_unique($allTheProjects);
		//abc order for all
		natcasesort($allTheProjects);
		//appened all option to the front
		$allTheProjects = $allOption + $allTheProjects;
		
		return $allTheProjects;
	}
	
	protected function extractEmployeesFromCards($dropdownRecords){
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
		natcasesort($employeeValues);
		//append all option to the front
		$employeeValues = [""=>"All"] + $employeeValues;
		
		return $employeeValues;
	}
	
	/**
    * Check if there is at least one card to be approved
    * @param $cardArr
    * @return boolean
    */
    protected function checkUnapprovedCardExist($type, $cardArr){
        foreach ($cardArr as $item){
            if ($item[$type.'ApprovedFlag'] == 0){
                return true;
            }
        }
        return false;
    }

    /**
    * Check if project was submitted ie Oasis or QB
    * @param $cardArray
    * @return boolean
    */
    protected function checkAllAssetsSubmitted($type, $cardArray){
        foreach ($cardArray as $item)
		{
			$oasisKey = array_key_exists($type.'OasisSubmitted', $item) ? $type.'OasisSubmitted' : 'OasisSubmitted';
			$qbKey = array_key_exists($type.'MSDynamicsSubmitted', $item) ? $type.'MSDynamicsSubmitted' : 'MSDynamicsSubmitted';
			
			if ($item[$oasisKey] == "No" || $item[$qbKey] == "No" ){
				return false;
			}
        }
        return true;
    }
	
	protected function getCardsByProject($projectID, $startDate, $endDate, $type, $filter = null, $employeeID = null){
		//url decode filter value
		$filter = urldecode($filter);
		//explode by delimiter to allow for multi search
		$delimiter = ',';
		$filterArray = explode($delimiter, $filter);
		
		//determine function to use based on type
		if($type == Constants::NOTIFICATION_TYPE_TIME){
			$function = 'fnTimeCardByDate';
			$idName = 'TimeCardProjectID';
		}elseif($type == Constants::NOTIFICATION_TYPE_MILEAGE){
			$function = 'fnMileageCardByDatePerformance';
			$idName = 'MileageCardProjectID';
		}
		$query = new Query;
		$cardQuery = $query->select('*')
			->from(["$function(:startDate, :endDate)"])
			->addParams([':startDate' => $startDate, ':endDate' => $endDate])
			->where([$idName => $projectID]);
			
		//add employeeID filter
		if($employeeID != null)
			$cardQuery->andWhere(['UserID' => $employeeID]);
		
		if($filterArray!= null){
			//initialize array for filter query values
			$filterQueryArray = array('or');
			//loop for multi search
			for($i = 0; $i < count($filterArray); $i++){
				//remove leading space from filter string
				$trimmedFilter = trim($filterArray[$i]);
				array_push($filterQueryArray,
					['like', 'ProjectName', $trimmedFilter],
					['like', 'UserFullName', $trimmedFilter]
				);
			}
			$cardQuery->andFilterWhere($filterQueryArray);
		}
			
		$cards = $cardQuery->orderBy('UserFullName ASC')
			->all(BaseActiveRecord::getDb());
			
		return $cards;
	}
}