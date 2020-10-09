<?php

namespace app\modules\v3\controllers;

use app\modules\v3\constants\Constants;
use app\modules\v3\models\Alert;
use app\modules\v3\models\SCUser;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\BreadCrumbChanged;
use app\modules\v3\models\BreadCrumbDelta;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Query;
use function file_get_contents;
use function json_decode;
use function json_encode;
use function print_r;

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
				'update' => ['put'],
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
			$startDate = $data["startDate"];
			$endDate = $data["endDate"];
			Yii::trace("Data params: " . $UserIDs . ", startDate: " . $startDate . ", endDate: " . $endDate . ", " . $supervisorID);
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
                ->where(['UserID' => (int)$userID])
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
					->addParams([':UserID' => $username, ':thisDate' => $date])
					->where('EndTime is not NULL')
					->orderBy('StartTime, EndTime');

			$stubHoursBreakdownQueryArrayRes = $stubHoursBreakdownQuery->all($db); 
			yii::trace(json_encode($stubHoursBreakdownQueryArrayRes));
			$stubHoursBreakdown = [];
			if(!empty($stubHoursBreakdownQueryArrayRes)){
				$i = 0;
				foreach ($stubHoursBreakdownQueryArrayRes as $key => $value){
					$stubHoursBreakdown[] = [
						'RowID' => $value['RowID'],
						'ProjectID' => $value['ProjectID'],
						'Project' => $value['ProjectName'],
						'TaskID' => $value['TaskID'],
						'TaskName' => $value['BreadcrumbActivityType'],
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
			$response->data = $responseArray;

			return $response;
		}catch(ForbiddenHttpException $e) {
            BaseActiveController::logError($e, 'Forbidden http exception');
			throw $e;
		}catch(\Exception $e){
		    throw $e;
		   throw new \yii\web\HttpException(400);
		}
	}

    /**
     *
     * @return \yii\console\Response|Response
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\HttpException
     */
    public function actionCreate()
    {
        //set target db
        BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

        //create db transaction
        $connection = BaseActiveRecord::getDb();
        $transaction = $connection->beginTransaction();

        try {

            //capture body
            $put = file_get_contents("php://input");
            yii::trace($put);
            $data = json_decode($put, true);

            //create response
            $success = true;
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //get username and current date
            $changedBy = BaseActiveController::getUserFromToken()->UserName;
            $changedOn = BaseActiveController::getDate();

            //archive json
            BaseActiveController::archiveWebJson(json_encode($data), 'Employee Detail Create', $changedBy,
                BaseActiveController::urlPrefix());

            // Always get username from user_id
            $user = SCUser::find()
                ->where(['UserID'=>$data['New']['UserID']])
                ->one();

            //
            $breadCrumbChanged = new BreadCrumbChanged();
            $breadCrumbChanged->OriginalRowID = 0;
            $breadCrumbChanged->BreadCrumbID=0;
            $breadCrumbChanged->ProjectID = $data['New']['ProjectID'];
            $breadCrumbChanged->TaskID = $data['New']['TaskID'];
            $breadCrumbChanged->BreadcrumbActivityType = $data['New']['TaskName'];
            $breadCrumbChanged->BreadcrumbSrcDTLT = $data['New']['StartTime'];
            $breadCrumbChanged->EndDate = $data['New']['EndTime'];
            $breadCrumbChanged->ChangedBy = $changedBy;
            $breadCrumbChanged->ChangedOn = $changedOn;
            $breadCrumbChanged->BreadcrumbCreatedUserUID = $user->UserName;


            if(!$breadCrumbChanged->save()){
                //  $transaction->rollBack();
               throw new \Exception(print_r($breadCrumbChanged->getErrors(),1));
            }

            if (ArrayHelper::keyExists('Current', $data)) {

                $originalRecord = BreadCrumbChanged::find()
                    ->where(['RowId'=>$data['Current']['ID']])
                    ->one();

                // if original record exists update it, otherwise create it
                if($originalRecord){

                    $deltaRecord = new BreadCrumbDelta();
                    $deltaRecord->OriginalRowID = $originalRecord->RowID;
                    $deltaRecord->ProjectID = $originalRecord->ProjectID;
                    $deltaRecord->BreadCrumbID = $originalRecord->BreadCrumbID;
                    $deltaRecord->BreadcrumbSrcDTLT = $originalRecord->BreadcrumbSrcDTLT;
                    $deltaRecord->EndDate = $originalRecord->EndDate;
                    $deltaRecord->TaskID = $originalRecord->TaskID;
                    $deltaRecord->Activity = $originalRecord->BreadcrumbActivityType;
                    $deltaRecord->UserName = $originalRecord->BreadcrumbCreatedUserUID;
                    $deltaRecord->ChangedBy = $changedBy;
                    $deltaRecord->ChangedOn = $changedOn;

                    if (!$deltaRecord->save()) {
                        throw new \Exception(print_r($deltaRecord->getErrors(),true));
                    }

                    //
                    $originalRecord->BreadcrumbSrcDTLT = $data['Current']['StartTime'];
                    $originalRecord->EndDate = $data['Current']['EndTime'];
                    $originalRecord->ChangedBy = $changedBy;
                    $originalRecord->ChangedOn = $changedOn;

                    if(!$originalRecord->save()){
                        throw new \Exception(print_r($originalRecord->getErrors(),true));
                    }
                }
            }

            // Add LogoutActivity if does not exist
            if (ArrayHelper::keyExists('LogoutActivity', $data)) {

                //
                $breadCrumbChanged = new BreadCrumbChanged();
                $breadCrumbChanged->OriginalRowID = 0;
                $breadCrumbChanged->BreadCrumbID = 0;
                $breadCrumbChanged->ProjectID = $data['LogoutActivity']['ProjectID'];
                $breadCrumbChanged->TaskID = $data['LogoutActivity']['TaskID'];
                $breadCrumbChanged->BreadcrumbActivityType = $data['LogoutActivity']['TaskName'];
                $breadCrumbChanged->BreadcrumbSrcDTLT = $data['LogoutActivity']['StartTime'];
                $breadCrumbChanged->EndDate = $data['LogoutActivity']['EndTime'];
                $breadCrumbChanged->ChangedBy = $changedBy;
                $breadCrumbChanged->ChangedOn = $changedOn;
                $breadCrumbChanged->BreadcrumbCreatedUserUID = $user->UserName;

                //
                if (!$breadCrumbChanged->save()) {
                    //  $transaction->rollBack();
                    throw new \Exception(print_r($breadCrumbChanged->getErrors(), 1));
                }
            }

            $transaction->commit();

            $status['success'] = $success;
            $response->data = $status;
            return $response;
        } catch (ForbiddenHttpException $e) {
            $transaction->rollBack();
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            //archive error

            throw $e;
            BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e,
                BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     *
     * @return \yii\console\Response|Response
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\HttpException
     */
    public function actionCreateInitial()
    {
        //set target db
        BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

        //create db transaction
        $connection = BaseActiveRecord::getDb();
        $transaction = $connection->beginTransaction();

        try {

            //capture body
            $post = file_get_contents("php://input");
            yii::trace($post);
            $data = json_decode($post, true);

            //create response
            $success = true;
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //get username and current date
            $changedBy = BaseActiveController::getUserFromToken()->UserName;
            $changedOn = BaseActiveController::getDate();

            //archive json
            BaseActiveController::archiveWebJson(json_encode($data), 'Employee Detail Create', $changedBy,
                BaseActiveController::urlPrefix());

            // Always get username from user_id
            $user = SCUser::find()
                ->where(['UserID' => $data['New']['UserID']])
                ->one();

            //
            foreach ($data as $key => $dataToAdd) {

                //
                $breadCrumbChanged = new BreadCrumbChanged();
                $breadCrumbChanged->OriginalRowID = 0;
                $breadCrumbChanged->BreadCrumbID=0;
                $breadCrumbChanged->ProjectID = $dataToAdd['ProjectID'];
                $breadCrumbChanged->TaskID = $dataToAdd['TaskID'];
                $breadCrumbChanged->BreadcrumbActivityType = $dataToAdd['TaskName'];
                $breadCrumbChanged->BreadcrumbSrcDTLT = $dataToAdd['StartTime'];
                $breadCrumbChanged->EndDate = $dataToAdd['EndTime'];
                $breadCrumbChanged->ChangedBy = $changedBy;
                $breadCrumbChanged->ChangedOn = $changedOn;
                $breadCrumbChanged->BreadcrumbCreatedUserUID = $user->UserName;

                if(!$breadCrumbChanged->save()){
                    //  $transaction->rollBack();
                    throw new \Exception(print_r($breadCrumbChanged->getErrors(),1));
                }
            }

            //
            $transaction->commit();

            $status['success'] = $success;
            $response->data = $status;
            return $response;
        } catch (ForbiddenHttpException $e) {
            $transaction->rollBack();
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            //archive error

            throw $e;
            BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e,
                BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
    }
	
	public function actionUpdate(){
		try{
			//set target db
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			//create response
			$success = true;
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;

			//get username and current date
			$changedBy = BaseActiveController::getUserFromToken()->UserName;
			$changedOn = BaseActiveController::getDate();

			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Employee Detail Edit', $changedBy, BaseActiveController::urlPrefix());
			
			//create db transaction
			$connection = BaseActiveRecord::getDb();
			$transaction = $connection->beginTransaction();
			
			foreach($data as $key=>$record){
				//skip empty rows
				if($record['ID'] != ''){
					//fetch original record
					$originalRecord = BreadCrumbChanged::findOne($record['ID']);
					//save original record to delta table
					$deltaRecord = new BreadCrumbDelta;
					$deltaRecord->OriginalRowID = $originalRecord->RowID;
					$deltaRecord->ProjectID = $originalRecord->ProjectID;
					$deltaRecord->BreadCrumbID = $originalRecord->BreadCrumbID;
					$deltaRecord->BreadcrumbSrcDTLT = $originalRecord->BreadcrumbSrcDTLT;
					$deltaRecord->EndDate = $originalRecord->EndDate;
					$deltaRecord->TaskID = $originalRecord->TaskID;
					$deltaRecord->Activity = $originalRecord->BreadcrumbActivityType;
					$deltaRecord->UserName = $originalRecord->BreadcrumbCreatedUserUID;
					$deltaRecord->ChangedBy = $changedBy;
					$deltaRecord->ChangedOn = $changedOn;
					if($deltaRecord->save()){
						//update original record
						$originalRecord->ProjectID = $record['ProjectID'];
						$originalRecord->TaskID = $record['TaskID'];
						if($record['TaskName'] != '') $originalRecord->BreadcrumbActivityType = $record['TaskName'];
						$originalRecord->BreadcrumbSrcDTLT = $record['StartTime'];
						$originalRecord->EndDate = $record['EndTime'];
						$originalRecord->ChangedBy = $changedBy;
						$originalRecord->ChangedOn = $changedOn;
						if(!$originalRecord->update()){
							$transaction->rollBack();
							$success = false;
							break;
						}
					}else{
						$transaction->rollBack();
						$success = false;
						break;
					}
				}
			}
			$transaction->commit();
			
			$status['success'] = $success;
			$response->data = $status;
			return $response;
		}catch(ForbiddenHttpException $e) {
			$transaction->rollBack();
            BaseActiveController::logError($e, 'Forbidden http exception');
			throw $e;
		}catch(\Exception $e){
			$transaction->rollBack();
			//archive error
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
		}
	}
	
}