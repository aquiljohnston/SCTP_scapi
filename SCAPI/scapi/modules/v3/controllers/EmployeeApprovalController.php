<?php

namespace app\modules\v3\controllers;

use app\modules\v3\constants\Constants;
use app\modules\v3\models\Alert;
use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Query;

class EmployeeApprovalController extends Controller 
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
		[
			'class' => VerbFilter::className(),
			'actions' => [
				'get' => ['get'],
				'create' => ['post'],
			],  
		];
		return $behaviors;	
	}	
        
	public function actionIndex(
		$startDate, 
		$endDate, 
		$listPerPage = 10, 
		$page = 1, 
		$filter = null, 
		$clientID = null, 
		$projectID = null,
		$sortField = 'UserFullName', 
		$sortOrder = 'ASC', 
		$employeeID = null
	)
	{
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

			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			//create db transaction
			$db = BaseActiveRecord::getDb();
			
			$transaction = $db->beginTransaction();
			
			//response array of time cards//
			$stubUserDataArray = [];
			$allOption = [];
			$showProjectDropDown = false;

			$dateHeders  = [];
			for($i=0;$i <=7;$i++){
				$dateHeders[$i] =  date('d/m/Y', strtotime($startDate)+$i*86400);
			}

			if($projectID == ''){
				$projectID = NULL;
			}

			$supervisorID = BaseActiveController::getUserFromToken()->UserID;
			//build base query
			$superviors = new Query;
			$superviors->select('*')
				->from(["fnReturnSupervisorByWeek(:ProjID, :SupervisorID, :startDate, :endDate)"])
				->addParams([':ProjID' => $projectID, ':SupervisorID' => $supervisorID, ':startDate' => $startDate, ':endDate' => $endDate]);
					
			$stubUserDataArrayRes = $superviors->all($db);    
                        
			if(!empty($stubUserDataArrayRes)){
				$summ_array  = array(
					'UserID' => null,
					'RowLabels' => null,
					$dateHeders[0] => 0,
					$dateHeders[1] => 0,
					$dateHeders[2] => 0,
					$dateHeders[3] => 0,
					$dateHeders[4] => 0,
					$dateHeders[5] => 0,
					$dateHeders[6] => 0,
					'Total' => 0,
					'PaidTimeOff' => 0,
					'Regular' => 0,
					'Overtime' => 0,
					'Expense' => 0,
					'MileageToApprove' => 0,
					'SupervisorApproved' => 0,
					'PMSubmitted' => 0,
					'ExceptionToResove' => 0
				);  
				foreach ($stubUserDataArrayRes as $key => $value){
					$stubUserDataArray[] = array(
						'UserID' => $value['UserID'],
						'RowLabels' => $value['UserName'],
						$dateHeders[0] => $value['day_1'],
						$dateHeders[1] => $value['day_2'],
						$dateHeders[2] => $value['day_3'],
						$dateHeders[3] => $value['day_4'],
						$dateHeders[4] => $value['day_5'],
						$dateHeders[5] => $value['day_6'],
						$dateHeders[6] => $value['day_7'],
						'Total' => $value['Total_Hrs'],
						'PaidTimeOff' => $value['PTO'],
						'Regular' => $value['Reg_Hrs'],
						'Overtime' => $value['OT'],
						'Expense' => $value['Expense'],
						'MileageToApprove' => $value['Mileage'],
						'SupervisorApproved' => $value['Supervisor_approval'],
						'PMSubmitted' => 'No',
						'ExceptionToResove' => '' 
					);
                                
					$summ_array = array(
						$dateHeders[0] => (float)$summ_array[$dateHeders[0]] + (float)$value['day_1'],
						$dateHeders[1] => (float)$summ_array[$dateHeders[1]] + (float)$value['day_2'],
						$dateHeders[2] => (float)$summ_array[$dateHeders[2]] + (float)$value['day_3'],
						$dateHeders[3] => (float)$summ_array[$dateHeders[3]] + (float)$value['day_4'],
						$dateHeders[4] => (float)$summ_array[$dateHeders[4]] + (float)$value['day_5'],
						$dateHeders[5] => (float)$summ_array[$dateHeders[5]] + (float)$value['day_6'],
						$dateHeders[6] => (float)$summ_array[$dateHeders[6]] + (float)$value['day_7'],
						'Total' => $summ_array['Total'] + (float)$value['Total_Hrs'],
						'PaidTimeOff' => $summ_array['PaidTimeOff'] + (float)$value['PTO'],
						'Regular' => $summ_array['Regular'] + (float)$value['Reg_Hrs'],
						'Overtime' => $summ_array['Overtime'] + (float)$value['OT'],
						'Expense' => $summ_array['Expense'] + (float)$value['Expense'],
						'MileageToApprove' => $summ_array['MileageToApprove'] + (float)$value['Mileage'],						
					);
				}
                            
				$stubUserDataArray[] = array(
					'UserID' => null,
					'RowLabels' => 'Grand Total',
					$dateHeders[0] => $summ_array[$dateHeders[0]],
					$dateHeders[1] => $summ_array[$dateHeders[1]],
					$dateHeders[2] => $summ_array[$dateHeders[2]],  
					$dateHeders[3] => $summ_array[$dateHeders[3]],  
					$dateHeders[4] => $summ_array[$dateHeders[4]],  
					$dateHeders[5] => $summ_array[$dateHeders[5]],  
					$dateHeders[6] => $summ_array[$dateHeders[6]],  
					'Total' => $summ_array['Total'],
					'PaidTimeOff' => $summ_array['PaidTimeOff'],
					'Regular' => $summ_array['Regular'],
					'Overtime' => $summ_array['Overtime'],
					'Expense' => $summ_array['Expense'],
					'MileageToApprove' =>  $summ_array['MileageToApprove'],
					'SupervisorApproved' => '',
					'PMSubmitted' => '',
					'ExceptionToResove' => ''
				);
			}
			
			$superviorsProj = new Query;
			$superviorsProj->select('*')
				->from(["fnReturnSupervisorProjects(:SupervisorID, :startDate, :endDate)"])
				->addParams([':SupervisorID' => $supervisorID, ':startDate' => $startDate, ':endDate' => $endDate]);
			
			$stubProjDataArray = [];
			$stubProjDataArrayRes= $superviorsProj->all($db);
                        
			if(!empty($stubProjDataArrayRes)){
				$summ_array  = array(
					'Projects' => '',
					$dateHeders[0] => 0,
					$dateHeders[1] => 0,
					$dateHeders[2] => 0,
					$dateHeders[3] => 0,
					$dateHeders[4] => 0,
					$dateHeders[5] => 0,
					$dateHeders[6] => 0,
					'Total' => 0,
					'PaidTimeOff' => 0,
					'Regular' => 0,
					'Overtime' => 0,
					'Expense' => 0,
					'Mileage' => 0,
				);  
				foreach ($stubProjDataArrayRes as $key => $value){
					$stubProjDataArray[] = array(
						'Projects' => $value['ProjectName'],
						$dateHeders[0] => $value['day_1'],
						$dateHeders[1] => $value['day_2'],
						$dateHeders[2] => $value['day_3'],  
						$dateHeders[3] => $value['day_4'],
						$dateHeders[4] => $value['day_5'],
						$dateHeders[5] => $value['day_6'],  
						$dateHeders[6] => $value['day_7'],  
						'Total' => $value['Total_Hrs'],
						'PaidTimeOff' => $value['PTO'],
						'Regular' => $value['Reg_hrs'],
						'Overtime' => $value['OT'],
						'Expense' => $value['Expense'],
						'Mileage' => $value['Mileage'],
					);
					$summ_array = array(
						'Projects' => '',
						$dateHeders[0] => $summ_array[$dateHeders[0]] + (float)$value['day_1'],
						$dateHeders[1] => $summ_array[$dateHeders[1]] + (float)$value['day_2'],
						$dateHeders[2] => $summ_array[$dateHeders[2]] + (float)$value['day_3'],
						$dateHeders[3] => $summ_array[$dateHeders[3]] + (float)$value['day_4'],
						$dateHeders[4] => $summ_array[$dateHeders[4]] + (float)$value['day_5'],
						$dateHeders[5] => $summ_array[$dateHeders[5]] + (float)$value['day_6'],
						$dateHeders[6] => $summ_array[$dateHeders[6]] + (float)$value['day_7'],
						'Total' => $summ_array['Total'] + (float)$value['Total_Hrs'],
						'PaidTimeOff' => $summ_array['PaidTimeOff'] + (float)$value['PTO'],
						'Regular' => $summ_array['Regular'] + (float)$value['Reg_hrs'],
						'Overtime' => $summ_array['Overtime'] + (float)$value['OT'],
						'Expense' =>  $summ_array['Expense'] + (float)$value['Expense'],
						'Mileage' => $summ_array['Mileage'] + (float)$value['Mileage'],
					);
				}

				$stubProjDataArray[] = array(
					'Projects' => 'Total',
					$dateHeders[0] => $summ_array[$dateHeders[0]],
					$dateHeders[1] => $summ_array[$dateHeders[1]],
					$dateHeders[2] => $summ_array[$dateHeders[2]],
					$dateHeders[3] => $summ_array[$dateHeders[3]],
					$dateHeders[4] => $summ_array[$dateHeders[4]],
					$dateHeders[5] => $summ_array[$dateHeders[5]],
					$dateHeders[6] => $summ_array[$dateHeders[6]],
					'Total' => $summ_array['Total'],
					'PaidTimeOff' =>  $summ_array['PaidTimeOff'],
					'Regular' =>  $summ_array['Regular'],
					'Overtime' =>  $summ_array['Overtime'],
					'Expense' =>  $summ_array['Expense'],
					'Mileage' =>   $summ_array['Mileage']
				);
			}

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
		}catch(ForbiddenHttpException $e) {
			throw $e;
		}catch(\Exception $e){
		   throw new \yii\web\HttpException(400);
		}
	}
	
}