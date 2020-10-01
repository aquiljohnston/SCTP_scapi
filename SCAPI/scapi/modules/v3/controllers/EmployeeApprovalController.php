<?php

namespace app\modules\v3\controllers;

use app\modules\v3\constants\Constants;
use app\modules\v3\models\Alert;
use app\modules\v3\models\SCUser;
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
				'approve-cards'  => ['put'],
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

			$dateHeaders  = [];
			for($i=0;$i <=7;$i++){
				$dateHeaders[$i] =  date('m/d/Y', strtotime($startDate)+$i*86400);
			}

			if($projectID == ''){
				$projectID = NULL;
			}

			$supervisorID = BaseActiveController::getUserFromToken()->UserID;
			Yii::trace("\nParams: " . $projectID . ', SupervisorID: ' . $supervisorID . ', startDate: ' . $startDate . ', endDate: ' . $endDate);
			//build base query
			$superviors = new Query;
			$superviors->select('*')
				->from(["fnReturnSupervisorByWeek(:ProjID, :SupervisorID, :startDate, :endDate)"])
				->addParams([':ProjID' => $projectID, ':SupervisorID' => $supervisorID, ':startDate' => $startDate, ':endDate' => $endDate]);
					
			$stubUserDataArrayRes = $superviors->all($db);    
                        
			if(!empty($stubUserDataArrayRes)){
				$sum_array  = array(
					'UserID' => null,
					'RowLabels' => null,
					$dateHeaders[0] => 0,
					$dateHeaders[1] => 0,
					$dateHeaders[2] => 0,
					$dateHeaders[3] => 0,
					$dateHeaders[4] => 0,
					$dateHeaders[5] => 0,
					$dateHeaders[6] => 0,
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
						$dateHeaders[0] => $value['day_1'],
						$dateHeaders[1] => $value['day_2'],
						$dateHeaders[2] => $value['day_3'],
						$dateHeaders[3] => $value['day_4'],
						$dateHeaders[4] => $value['day_5'],
						$dateHeaders[5] => $value['day_6'],
						$dateHeaders[6] => $value['day_7'],
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
                                
					$sum_array = array(
						$dateHeaders[0] => (float)$sum_array[$dateHeaders[0]] + (float)$value['day_1'],
						$dateHeaders[1] => (float)$sum_array[$dateHeaders[1]] + (float)$value['day_2'],
						$dateHeaders[2] => (float)$sum_array[$dateHeaders[2]] + (float)$value['day_3'],
						$dateHeaders[3] => (float)$sum_array[$dateHeaders[3]] + (float)$value['day_4'],
						$dateHeaders[4] => (float)$sum_array[$dateHeaders[4]] + (float)$value['day_5'],
						$dateHeaders[5] => (float)$sum_array[$dateHeaders[5]] + (float)$value['day_6'],
						$dateHeaders[6] => (float)$sum_array[$dateHeaders[6]] + (float)$value['day_7'],
						'Total' => $sum_array['Total'] + (float)$value['Total_Hrs'],
						'PaidTimeOff' => $sum_array['PaidTimeOff'] + (float)$value['PTO'],
						'Regular' => $sum_array['Regular'] + (float)$value['Reg_Hrs'],
						'Overtime' => $sum_array['Overtime'] + (float)$value['OT'],
						'Expense' => $sum_array['Expense'] + (float)$value['Expense'],
						'MileageToApprove' => $sum_array['MileageToApprove'] + (float)$value['Mileage'],						
					);
				}
                            
				$stubUserDataArray[] = array(
					'UserID' => null,
					'RowLabels' => 'Grand Total',
					$dateHeaders[0] => $sum_array[$dateHeaders[0]],
					$dateHeaders[1] => $sum_array[$dateHeaders[1]],
					$dateHeaders[2] => $sum_array[$dateHeaders[2]],  
					$dateHeaders[3] => $sum_array[$dateHeaders[3]],  
					$dateHeaders[4] => $sum_array[$dateHeaders[4]],  
					$dateHeaders[5] => $sum_array[$dateHeaders[5]],  
					$dateHeaders[6] => $sum_array[$dateHeaders[6]],  
					'Total' => $sum_array['Total'],
					'PaidTimeOff' => $sum_array['PaidTimeOff'],
					'Regular' => $sum_array['Regular'],
					'Overtime' => $sum_array['Overtime'],
					'Expense' => $sum_array['Expense'],
					'MileageToApprove' =>  $sum_array['MileageToApprove'],
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
            $projectDropdownDataArray = ['' => 'All',];
			if(!empty($stubProjDataArrayRes)){
				$sum_array  = array(
					'Projects' => '',
					$dateHeaders[0] => 0,
					$dateHeaders[1] => 0,
					$dateHeaders[2] => 0,
					$dateHeaders[3] => 0,
					$dateHeaders[4] => 0,
					$dateHeaders[5] => 0,
					$dateHeaders[6] => 0,
					'Total' => 0,
					'PaidTimeOff' => 0,
					'Regular' => 0,
					'Overtime' => 0,
					'Expense' => 0,
					'Mileage' => 0,
				);  
				foreach ($stubProjDataArrayRes as $key => $value){
					// push project to project dropdown
					$projectDropdownDataArray[$value['ProjectID']] = $value['ProjectName'];
					$stubProjDataArray[] = array(
						'Projects' => $value['ProjectName'],
						$dateHeaders[0] => $value['day_1'],
						$dateHeaders[1] => $value['day_2'],
						$dateHeaders[2] => $value['day_3'],  
						$dateHeaders[3] => $value['day_4'],
						$dateHeaders[4] => $value['day_5'],
						$dateHeaders[5] => $value['day_6'],  
						$dateHeaders[6] => $value['day_7'],  
						'Total' => $value['Total_Hrs'],
						'PaidTimeOff' => $value['PTO'],
						'Regular' => $value['Reg_hrs'],
						'Overtime' => $value['OT'],
						'Expense' => $value['Expense'],
						'Mileage' => $value['Mileage'],
					);
					$sum_array = array(
						'Projects' => '',
						$dateHeaders[0] => $sum_array[$dateHeaders[0]] + (float)$value['day_1'],
						$dateHeaders[1] => $sum_array[$dateHeaders[1]] + (float)$value['day_2'],
						$dateHeaders[2] => $sum_array[$dateHeaders[2]] + (float)$value['day_3'],
						$dateHeaders[3] => $sum_array[$dateHeaders[3]] + (float)$value['day_4'],
						$dateHeaders[4] => $sum_array[$dateHeaders[4]] + (float)$value['day_5'],
						$dateHeaders[5] => $sum_array[$dateHeaders[5]] + (float)$value['day_6'],
						$dateHeaders[6] => $sum_array[$dateHeaders[6]] + (float)$value['day_7'],
						'Total' => $sum_array['Total'] + (float)$value['Total_Hrs'],
						'PaidTimeOff' => $sum_array['PaidTimeOff'] + (float)$value['PTO'],
						'Regular' => $sum_array['Regular'] + (float)$value['Reg_hrs'],
						'Overtime' => $sum_array['Overtime'] + (float)$value['OT'],
						'Expense' =>  $sum_array['Expense'] + (float)$value['Expense'],
						'Mileage' => $sum_array['Mileage'] + (float)$value['Mileage'],
					);
				}

				$stubProjDataArray[] = array(
					'Projects' => 'Total',
					$dateHeaders[0] => $sum_array[$dateHeaders[0]],
					$dateHeaders[1] => $sum_array[$dateHeaders[1]],
					$dateHeaders[2] => $sum_array[$dateHeaders[2]],
					$dateHeaders[3] => $sum_array[$dateHeaders[3]],
					$dateHeaders[4] => $sum_array[$dateHeaders[4]],
					$dateHeaders[5] => $sum_array[$dateHeaders[5]],
					$dateHeaders[6] => $sum_array[$dateHeaders[6]],
					'Total' => $sum_array['Total'],
					'PaidTimeOff' =>  $sum_array['PaidTimeOff'],
					'Regular' =>  $sum_array['Regular'],
					'Overtime' =>  $sum_array['Overtime'],
					'Expense' =>  $sum_array['Expense'],
					'Mileage' =>   $sum_array['Mileage']
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
			
			$responseArray = [];
			$responseArray['UserData'] = $stubUserDataArray;
			$responseArray['ProjData'] = $stubProjDataArray;
			$responseArray['StatusData'] = $stubStatusDataArray;
			$responseArray['ProjectDropDown'] = $projectDropdownDataArray;
			
			//format response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;

			return $response;
		}catch(ForbiddenHttpException $e) {
            BaseActiveController::logError($e, 'Forbidden http exception');
			throw $e;
		}catch(\Exception $e){
		   throw new \yii\web\HttpException(400);
		}
	}

	public function actionApproveTimecards(){           
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			//get userid
			$approvedBy = BaseActiveController::getUserFromToken()->UserName;
			$supervisorID = BaseActiveController::getUserFromToken()->UserID;

			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Base Card Approve', $approvedBy, BaseActiveController::urlPrefix());
			
                        
			//create db transaction
			$connection = BaseActiveRecord::getDb();
			$transaction = $connection->beginTransaction();
                        
			$UserIDs = $data["cardIDArray"];
			         
			$resetCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spSupervisorTimeCardApproval :startDate, :endDate, :UserIDs,  :SupervisorID");
			$resetCommand->bindParam(':startDate', $startDate,  \PDO::PARAM_STR);
			$resetCommand->bindParam(':endDate', $endDate,  \PDO::PARAM_STR);
			$resetCommand->bindParam(':UserIDs', $UserIDs,  \PDO::PARAM_STR);
			$resetCommand->bindParam(':SupervisorID', $supervisorID,  \PDO::PARAM_STR);
			$resetCommand->execute();  
			$transaction->commit();
			$status  = array();
			$status['success'] = true;
			$response->data = $status;

			return $response;
		} catch (ForbiddenHttpException $e) {
			$transaction->rollBack();
            BaseActiveController::logError($e, 'Forbidden http exception');
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			$transaction->rollBack();
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
		}
	}

	public function actionEmployeeDetail($userID,$date){
		try{
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//create db transaction
			$db = BaseActiveRecord::getDb();

			$date = date('Y-m-d', strtotime($date));
			
			$user = SCUser::find()
                ->where(['UserID' => $userID])
                ->one();
			$username = $user->UserName;
			

			$stubHoursByProjectQuery = new Query;
			$stubHoursByProjectQuery->select('*')
					->from(["fnReturnDetailSummary(:UserID,:thisDate)"])
					->addParams([':UserID' => $username, ':thisDate' => $date]);

			$stubHoursByProjectArrayRes = $stubHoursByProjectQuery->all($db);

			$stubHoursByProject = [];
			if(!empty($stubHoursByProjectArrayRes)){
				$stubHoursByProject[] = [
					'Label' => 'Date',
					'Value' => $date,
				];
				foreach($stubHoursByProjectArrayRes as $key => $item){
					$stubHoursByProject[] = [
						'Label' => $item['ProjectName'],
						'Value' => $item['Hours'],
					];
				}
			}

			$stubHoursBreakdownQuery = new Query;
			$stubHoursBreakdownQuery->select('*')
					->from(["fnReturnDetails(:UserID,:thisDate)"])
					->addParams([':UserID' => $username, ':thisDate' => $date]);

			$stubHoursBreakdownQueryArrayRes = $stubHoursBreakdownQuery->all($db); 
			yii::trace(json_encode($stubHoursBreakdownQueryArrayRes));
			$stubHoursBreakdown = [];
			if(!empty($stubHoursBreakdownQueryArrayRes)){
				$i = 0;
				foreach ($stubHoursBreakdownQueryArrayRes as $key => $value){
					$stubHoursBreakdown[] = [
						'RowID' => $value['BreadCrumbID'],
						'ProjectID' => $value['ProjectID'],
						'Project' => $value['ProjectName'],
						'Task' => $value['BreadcrumbActivityType'],
						'Start Time' => $value['StartTime'],
						'End Time' => $value['EndTime'],
						'Time On Task' => $value['Duration']
					];
				}
			}

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
		}catch(ForbiddenHttpException $e) {
            BaseActiveController::logError($e, 'Forbidden http exception');
			throw $e;
		}catch(\Exception $e){
		   throw new \yii\web\HttpException(400);
		}
	}
	
}