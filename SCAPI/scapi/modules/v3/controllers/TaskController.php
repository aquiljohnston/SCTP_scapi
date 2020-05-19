<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\constants\Constants;
use app\modules\v3\models\Project;
use app\modules\v3\models\Task;
use app\modules\v3\models\TaskAndProject;
use app\modules\v3\models\ChartOfAccountType;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\PTO;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\authentication\TokenAuth;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\db\Query;

class TaskController extends Controller 
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
                    'get-by-project' => ['get'],
                    'get-all-task' => ['get'],
					'get-charge-of-account-type' => ['get'],
					'get-hours-overview' => ['get'],
					'create-task-entry' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGetByProject($projectID)
	{
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('taskGetByProject');

			$data['assets'] = TaskAndProject::find()
				->select(['TaskID', 'TaskName', 'TaskQBReferenceID', 'Category'])
				->where(['projectID' => $projectID])
				->orderBy(['TaskID' => SORT_ASC, 'TaskName' => SORT_ASC])
				->asArray()
				->all();

			//build and return response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
			return $response;
		} catch (yii\db\Exception $e) {
			throw $e;
		} catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            BaseActiveController::archiveWebErrorJson('Task GetByProject', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
		}
	}
	
	/*
	 * Get All Task From CT DB
	 * @return Json Array Of All Task
	 */
    public function actionGetAllTask($timeCardProjectID = null)
    {
        try {
            $responseArray = [];
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('getAllTask');

            // check if it is CT project
            $projectUrl = Project::find()
                ->select(['ProjectUrlPrefix'])
                ->where(['ProjectID' => $timeCardProjectID])
                ->one();

			if(BaseActiveController::isSCCT($projectUrl['ProjectUrlPrefix'])) {
                $data = Task::find()
                    ->select(['TaskID', 'TaskName', 'TaskQBReferenceID'])
					->orderBy(['TaskID' => SORT_ASC, 'TaskName' => SORT_ASC])
                    ->asArray()
                    ->all();
            } else {
                $data = self::GetProjectTask($timeCardProjectID);
            }
            $responseArray['assets'] = $data;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $responseArray;
            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            BaseActiveController::archiveWebErrorJson('actionGetTasks', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
    }
	
	/**
     * Get ChargeOfAccountType From CT DB
     * @return mixed
     */
    public function actionGetChargeOfAccountType($inOvertime = 'false'){
		try{
			//set db target
			ChartOfAccountType::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('getChargeOfAccount');
			
			$chartOfAccountQuery = ChartOfAccountType::find();
			
			if($inOvertime == 'true') $chartOfAccountQuery->where(['ChartOfAccountID' => Constants::OT_PAYROLL_HOURS_ID]);
			
			$chartOfAccountType = $chartOfAccountQuery->all();

			$namePairs = [];
			$codesSize = count($chartOfAccountType);

			for($i=0; $i < $codesSize; $i++)
			{
				$namePairs[$chartOfAccountType[$i]->ChartOfAccountID] = $chartOfAccountType[$i]->ChartOfAccountDescription;
			}

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
			return $response;
		} catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
	
	public function actionGetHoursOverview($timeCardID, $date){
		try {
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('taskGetHoursOverview');
			
			$hoursOverviewQuery = new Query;
			$hoursOverview = $hoursOverviewQuery->select('*')
				->from(["fnGetTaskIntervalsByTimeCard(:TimeCardID)"])
				->addParams([':TimeCardID' => $timeCardID])
				->where(['Date' => $date])
				->all(BaseActiveRecord::getDb());

			//format response
			$responseArray['hoursOverview'] = $hoursOverview;
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
		} catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            BaseActiveController::archiveWebErrorJson('actionGetHoursOverview', $e, getallheaders()['X-Client'], [$timeCardID, $date]);
            throw new \yii\web\HttpException(400);
        }
	}
	
	  /**
     * Create New Task Entry in CT DB
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreateTaskEntry()
    {
        try {
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('createTaskEntry');
			
			$successFlag = 0;
			$warningMessage = '';
			
            //get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
			
			$results = self::addActivityAndTime($data);
			$isPTO = $data['ChargeOfAccountType'] == Constants::PTO_PAYROLL_HOURS_ID;
			$ptoSuccessFlag = 0;
			if($isPTO && $results['successFlag']){
				//if task is pto and saved successfully create pto record
				$pto = new PTO;
				$pto->attributes = $data['PTOData'];
				if ($pto->save()){
					$ptoSuccessFlag  = 1;
				} else {
					throw BaseActiveController::modelValidationException($pto);
				}
			}
			$successFlag = $results['successFlag'];
			$warningMessage = $results['warningMessage'];			

        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], [
                'TimeCardID' => $data['TimeCardID'],
                'TaskName' => $data['TaskName'],
                'Date' => $data['Date'],
                'StartTime' => $data['StartTime'],
                'EndTime' => $data['EndTime'],
                'CreatedByUserName' => $data['CreatedByUserName'],
                'ChargeOfAccountType' => $data['ChargeOfAccountType'],
                'SuccessFlag' => $successFlag
            ]);
        }

        //build response format
        $dataArray =  [
            'TimeCardID' => $data['TimeCardID'],
            'SuccessFlag' => $successFlag,
			'warningMessage' => $warningMessage
        ];
		//if is pto add to response
		if($isPTO) $dataArray['PTOSuccessFlag'] = $ptoSuccessFlag;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $dataArray;
    }
	
	public static function addActivityAndTime($data){
		$successFlag = 0;
		$warningMessage = '';
		
		//check time overlap on new entry
		$startDateTime = $data['Date'] . ' ' . $data['StartTime'];
		$endDateTime = $data['Date'] . ' ' . $data['EndTime'];
		$isOverlap = self::checkTimeOverlap($data['TimeCardID'], $startDateTime, $endDateTime);

		if($isOverlap ==0){
			//remove charge of account that is causing conflict in fnGeneratePayrollDataByProject
			if(!in_array($data['ChargeOfAccountType'], [Constants::PTO_PAYROLL_HOURS_ID, Constants::HOLIDAY_BEREAVEMENT_PAYROLL_HOURS_ID])) $data['ChargeOfAccountType'] = NULL;
			
			// set up db connection
			$connection = BaseActiveRecord::getDb();
			$processJSONCommand = $connection->createCommand("EXECUTE spAddActivityAndTime :TimeCardID, :TaskName , :Date, :StartTime, :EndTime, :CreatedByUserName, :ChargeOfAccountType, :TimeReason");
			$processJSONCommand->bindParam(':TimeCardID', $data['TimeCardID'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':TaskName', $data['TaskName'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':Date', $data['Date'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':StartTime', $data['StartTime'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':EndTime', $data['EndTime'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':CreatedByUserName', $data['CreatedByUserName'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':ChargeOfAccountType', $data['ChargeOfAccountType'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':TimeReason', $data['TimeReason'], \PDO::PARAM_STR);
			$processJSONCommand->execute();
			$successFlag = 1;
		}else{
			$warningMessage = 'Failed to save, new entry overlaps with existing time.';
		}
		
		$results = [
			'successFlag' => $successFlag,
			'warningMessage' => $warningMessage
		];
		
		return $results;
	}
	
	private static function checkTimeOverlap($cardID, $startTime, $endTime)
	{
		$overlapCheck = new Query;
		$overlapCheck->select('*')
					->from(["fnIsTimeEntryOverlap(:cardID, :startTime, :endTime)"])
					->addParams([
					':cardID' => $cardID,
					':startTime' => $startTime,
					':endTime' => $endTime
					]);
		$isOverlap = $overlapCheck->one(BaseActiveRecord::getDb())['IsOverLap'];

		return $isOverlap;
	}
}