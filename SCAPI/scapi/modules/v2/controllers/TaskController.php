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
					'create-task-entry' => ['post'],
					'get-charge-of-account-type' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	//does this need to be public?
	public static function GetProjectTask($projectID)
	{
        //set db target
        TaskAndProject::setClient(BaseActiveController::urlPrefix());

        $responseArray = [];
        $data = TaskAndProject::find()
            ->where(['projectID' => $projectID])
            ->asArray()
            ->all();

        $responseArray = $data != null ? $data : [];
		return $responseArray;
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
    public function actionGetChargeOfAccountType(){
        //set db target
        ChartOfAccountType::setClient(BaseActiveController::urlPrefix());

        $chartOfAccountType = ChartOfAccountType::find()
            ->all();

        $namePairs = [];
        $codesSize = count($chartOfAccountType);

        for($i=0; $i < $codesSize; $i++)
        {
            $namePairs[$chartOfAccountType[$i]->ChartOfAccountID]= $chartOfAccountType[$i]->ChartOfAccountDescription;
        }


        $response = Yii::$app ->response;
        $response -> format = Response::FORMAT_JSON;
        $response -> data = $namePairs;

        return $response;
    }
	
	  /**
     * Create New Task Entry in CT DB
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreateTaskEntry()
    {
        $successFlag = 0;
        try {
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);

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
            'SuccessFlag' => $successFlag
        ];
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $dataArray;
    }
}