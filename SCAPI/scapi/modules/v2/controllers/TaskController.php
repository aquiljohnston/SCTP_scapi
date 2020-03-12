<?php

namespace app\modules\v2\controllers;

use app\modules\v2\constants\Constants;
use app\modules\v2\models\Project;
use Yii;
use yii\rest\Controller;
use app\modules\v2\models\Task;
use app\modules\v2\models\TaskAndProject;
use app\modules\v2\models\ChartOfAccountType;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
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
                    'get-all-task' => ['get'],
                    'get-by-project' => ['get'],
					'create-task-entry' => ['post'],
					'get-charge-of-account-type' => ['get'],
					'get-hours-overview' => ['get'],
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

			$data['assets'] = self::getTask($projectID);

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
			
			yii::trace('JSON BODY ' . $body);
			
			//check time overlap on new entry
			$startDateTime = $data['Date'] . ' ' . $data['StartTime'];
			$endDateTime = $data['Date'] . ' ' . $data['EndTime'];
			$isOverlap = self::checkTimeOverlap($data['TimeCardID'], $startDateTime, $endDateTime);
			
			if($isOverlap ==0)
			{
				// set up db connection
				$connection = BaseActiveRecord::getDb();
				$processJSONCommand = $connection->createCommand("EXECUTE spAddActivityAndTime :TimeCardID, :TaskName , :Date, :StartTime, :EndTime, :CreatedByUserName, :ChargeOfAccountType");
				$processJSONCommand->bindParam(':TimeCardID', $data['TimeCardID'], \PDO::PARAM_STR);
				$processJSONCommand->bindParam(':TaskName', $data['TaskName'], \PDO::PARAM_STR);
				$processJSONCommand->bindParam(':Date', $data['Date'], \PDO::PARAM_STR);
				$processJSONCommand->bindParam(':StartTime', $data['StartTime'], \PDO::PARAM_STR);
				$processJSONCommand->bindParam(':EndTime', $data['EndTime'], \PDO::PARAM_STR);
				$processJSONCommand->bindParam(':CreatedByUserName', $data['CreatedByUserName'], \PDO::PARAM_STR);
				$processJSONCommand->bindParam(':ChargeOfAccountType', $data['ChargeOfAccountType'], \PDO::PARAM_STR);
				$processJSONCommand->execute();
				$successFlag = 1;
			}
			else
			{
				$warningMessage = 'Failed to save, new entry overlaps with existing time.';
			}

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
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $dataArray;
    }
	
	public static function getTask($projectID){
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		return TaskAndProject::find()
			->select(['TaskID', 'TaskName', 'TaskReferenceID', 'Category'])
			->where(['projectID' => $projectID])
			->orderBy(['TaskID' => SORT_ASC, 'TaskName' => SORT_ASC])
			->asArray()
			->all();
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
		
		yii::trace('IS OVERLAP ' . json_encode($isOverlap));
		
		return $isOverlap;
	}
}