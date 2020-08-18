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
	//default model class to allow route access
	public $modelClass = 'app\modules\v3\models\BaseActiveRecord';
	
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
					'report-summary' => ['get'],
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
	
	public function actionReportSummary($startDate, $endDate, $listPerPage = 10, $page = 1, $filter = null, $clientID = null, $projectID = null,
		$sortField = 'UserFullName', $sortOrder = 'ASC', $employeeID = null)
	{
		// try{
			$stubUserDataArray = [
				[
					'UserID' => 10000,
					'RowLabels' => 'Andrew Harris',
					'7/5/2020' => '-',
					'7/6/2020' => '8.4',
					'7/7/2020' => '12',
					'7/8/2020' => '8',
					'7/9/2020' => '8',
					'7/10/2020' => '10',
					'7/11/2020' => '-',
					'Total' => '46.4',
					'PaidTimeOff' => '2.0',
					'Regular' => '44.4',
					'Overtime' => '4.4',
					'MileageToApprove' => '136.0',
					'SupervisorApproved' => 'Yes',
					'PMSubmitted' => 'No',
					'ExceptionToResove' => ''
				],
				[
					'UserID' => 7286,
					'RowLabels' => 'Angel Valdez',
					'7/5/2020' => '-',
					'7/6/2020' => '9',
					'7/7/2020' => '9',
					'7/8/2020' => '9',
					'7/9/2020' => '10',
					'7/10/2020' => '8.5',
					'7/11/2020' => '-',
					'Total' => '45.5',
					'PaidTimeOff' => '5.0',
					'Regular' => '40.5',
					'Overtime' => '0.5',
					'MileageToApprove' => '12.0',
					'SupervisorApproved' => 'Yes',
					'PMSubmitted' => 'No',
					'ExceptionToResove' => 'Yes'
				],
				[
					'UserID' => 11848,
					'RowLabels' => 'Enson Nzungize',
					'7/5/2020' => '-',
					'7/6/2020' => '8',
					'7/7/2020' => '9',
					'7/8/2020' => '10',
					'7/9/2020' => '8',
					'7/10/2020' => '9',
					'7/11/2020' => '-',
					'Total' => '44',
					'PaidTimeOff' => '4.0',
					'Regular' => '40',
					'Overtime' => '-',
					'MileageToApprove' => '28.0',
					'SupervisorApproved' => 'No',
					'PMSubmitted' => 'No',
					'ExceptionToResove' => ''
				],
				[
					'UserID' => null,
					'RowLabels' => 'Grand Total',
					'7/5/2020' => '-',
					'7/6/2020' => '90.9',
					'7/7/2020' => '121.8',
					'7/8/2020' => '111.5',
					'7/9/2020' => '112.0',
					'7/10/2020' => '93.8',
					'7/11/2020' => '-',
					'Total' => '529.9',
					'PaidTimeOff' => '15.0',
					'Regular' => '514.9',
					'Overtime' => '7.4',
					'MileageToApprove' => '504.2',
					'SupervisorApproved' => '',
					'PMSubmitted' => '',
					'ExceptionToResove' => ''
				]
			];
			
			$stubProjDataArray = [
				[
					'Projects' => 'CPS',
					'7/5/2020' => '-',
					'7/6/2020' => '58.9',
					'7/7/2020' => '67.3',
					'7/8/2020' => '59.5',
					'7/9/2020' => '56.0',
					'7/10/2020' => '52.0',
					'7/11/2020' => '-',
					'Total' => '293.6',
					'PaidTimeOff' => '15.0',
					'Regular' => '278.6',
					'Overtime' => '5.9',
					'Mileage' => '344.2',
				],
				[
					'Projects' => 'JBSA Lackland',
					'7/5/2020' => '-',
					'7/6/2020' => '32',
					'7/7/2020' => '54.5',
					'7/8/2020' => '52.0',
					'7/9/2020' => '56.0',
					'7/10/2020' => '41.8',
					'7/11/2020' => '-',
					'Total' => '236.3',
					'PaidTimeOff' => '-',
					'Regular' => '236.3',
					'Overtime' => '1.5',
					'Mileage' => '160.0',
				],
				[
					'Projects' => 'Total',
					'7/5/2020' => '-',
					'7/6/2020' => '90.9',
					'7/7/2020' => '121.8',
					'7/8/2020' => '111.5',
					'7/9/2020' => '112.0',
					'7/10/2020' => '93.8',
					'7/11/2020' => '-',
					'Total' => '529.9',
					'PaidTimeOff' => '15.0',
					'Regular' => '514.9',
					'Overtime' => '7.4',
					'Mileage' => '504.2'
				]
			];
			
			$stubStatusDataArray = [
				[
					'Validations' => 'Exceptions Resolved',
					'Status' => '85%'
				],
				[
					'Validations' => 'Percent Approved',
					'Status' => '67%'
				]
			];
			
			$projectDropDown = [
				'' => 'All',
				'1' => 'CPS',
				'2' => 'JBSA Lackland',
			];
			
			$responseArray = [];
			$responseArray['UserData'] = $stubUserDataArray;
			$responseArray['ProjData'] = $stubProjDataArray;
			$responseArray['StatusData'] = $stubStatusDataArray;
			$responseArray['ProjectDropDown'] = $projectDropDown;
			
			//format response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		// }catch(ForbiddenHttpException $e) {
			// throw $e;
		// }catch(\Exception $e){
		   // throw new \yii\web\HttpException(400);
		// }
	}
	
	public function actionApprove(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			// RBAC permission check
			PermissionsController::requirePermission('timeCardApproveCards');
			PermissionsController::requirePermission('mileageCardApproveCards');

			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			//get userid
			$approvedBy = self::getUserFromToken()->UserName;

			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Base Card Approve', $approvedBy, BaseActiveController::urlPrefix());
			
			
			// catch(\Exception $e) //if transaction fails rollback changes and send error
			// {
				// $transaction->rollBack();
				// //archive error
				// BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
				// $response->setStatusCode(400);
				// $response->data = "Http:400 Bad Request";
				// return $response;

			// }
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionEmployeeDetail($userID, $startDate){
		// try{
			$stubHoursByProject = [
				[
					'Label' => 'Date',
					'Value' => '7/6/2020',
				],
				[
					'Label' => 'CPS Energy 83743',
					'Value' => '6:22',
				],
				[
					'Label' => 'CPS JBSA Lackland 83643',
					'Value' => '1:45',
				],
				[
					'Label' => 'Daily Total',
					'Value' => '8:07',
				]
			];
			
			$stubHoursBreakdown = [
				[
					'RowID' => 1,
					'Project' => 'CPS Energy 83743',
					'Task' => 'Employee Login',
					'Start Time' => '7:51',
					'End Time' => '',
					'Time On Task' => ''
				],
				[
					'RowID' => 2,
					'Project' => 'CPS Energy 83743',
					'Task' => 'Task 5 YEAR',
					'Start Time' => '7:51',
					'End Time' => '11:59',
					'Time On Task' => '4:08'
				],
				[
					'RowID' => 3,
					'Project' => 'CPS Energy 83743',
					'Task' => 'Lunch',
					'Start Time' => '11:59',
					'End Time' => '12:29',
					'Time On Task' => '0:30'
				],
				[
					'RowID' => 4,
					'Project' => 'CPS JBSA Lackland 83643',
					'Task' => 'Task 5 YEAR',
					'Start Time' => '12:29',
					'End Time' => '16:29',
					'Time On Task' => '4:00'
				],
				[
					'RowID' => 5,
					'Project' => 'CPS JBSA Lackland 83643',
					'Task' => 'Employee Logout',
					'Start Time' => '',
					'End Time' => '16:29',
					'Time On Task' => ''
				],
			];
			
			$stubTotals = [
				'Tech' => 'Andrew Harris',
				'WeeklyTotal' => '40.2',
				'Total' => '8:37',
				'TotalNoLunch' => '8:07',
			];
			
			$responseArray = [];
			$responseArray['ProjectData'] = $stubHoursByProject;
			$responseArray['BreakdownData'] = $stubHoursBreakdown;
			$responseArray['Totals'] = $stubTotals;
			
			//format response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		// }catch(ForbiddenHttpException $e) {
			// throw $e;
		// }catch(\Exception $e){
		   // throw new \yii\web\HttpException(400);
		// }
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