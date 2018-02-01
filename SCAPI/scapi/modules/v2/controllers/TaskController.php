<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use app\modules\v2\models\Task;
use app\modules\v2\models\TaskAndProject;
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
					'get-project-task' => ['get'],
					'get-project-user-task' => ['get'],
                    'get-all-task' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public static function GetProjectTask($projectID)
	{
        //set db target
        TaskAndProject::setClient(BaseActiveController::urlPrefix());

        $responseArray = [];
        $data = TaskAndProject::find()
            ->where(['projectID' => $projectID])
            ->asArray()
            ->all();

        $responseArray['assets'] = $data != null ? $data : [];
		return $responseArray;
	}
	
	public static function GetProjectUserTask()
	{
		//TODO add call to db
		return [
			[
				'FilterName' => 'Training',
				'SortSeq' => 1,
				'FieldDisplayValue' => 'Training',
			],[
				'FilterName' => 'Leak Survey',
				'SortSeq' => 2,
				'FieldDisplayValue' => 'Leak Survey',
			],
		];
	}

	/*
	 * Get All Task From CT DB
	 * @return Json Array Of All Task
	 */
	public function actionGetAllTask(){
        try{
            $responseArray = [];
            //set db target
            Task::setClient(BaseActiveController::urlPrefix());

            $userQuery = Task::find()
                ->select(['TaskID', 'TaskName', 'TaskQBReferenceID']);

            $data = $userQuery -> orderBy(['TaskID'=>SORT_ASC, 'TaskName'=>SORT_ASC])
                               ->asArray()
                               ->all();
            $responseArray['assets'] = $data;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $responseArray;
            return $response;
        }
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            BaseActiveController::archiveWebErrorJson('actionGetTasks', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
    }
}