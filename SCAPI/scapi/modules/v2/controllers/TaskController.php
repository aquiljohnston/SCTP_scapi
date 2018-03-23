<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
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
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGetProjectTask()
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
			],[
				'FilterName' => 'Atmospheric Corrosion',
				'SortSeq' => 3,
				'FieldDisplayValue' => 'Atmospheric Corrosion',
			],
		];
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
            Task::setClient(BaseActiveController::urlPrefix());

            // check if it is CT project
            $projectUrl = Project::find()
                ->select(['ProjectUrlPrefix'])
                ->where(['ProjectID' => $timeCardProjectID])
                ->one();

			if(BaseActiveController::isSCCT($projectUrl)) {

                $userQuery = Task::find()
                    ->select(['TaskID', 'TaskName', 'TaskQBReferenceID']);

                $data = $userQuery->orderBy(['TaskID' => SORT_ASC, 'TaskName' => SORT_ASC])
                    ->asArray()
                    ->all();
            } else {
                $data = self::GetProjectTask($timeCardProjectID);
            }
            $responseArray['assets'] = $data['assets'];

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
}