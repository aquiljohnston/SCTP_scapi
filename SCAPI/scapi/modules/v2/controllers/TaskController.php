<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use app\modules\v2\models\Task;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
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
	
	public function actionGetProjectTask($projectID)
	{
		
		/*$response = Yii::$app ->response;

		$taskQuery = new Query;

		$taskQuery->select('*')
					->from("vTaskAndProject(:projectID)")
					->addParams([':projectID' => $projectID]);

		$tasks = $taskQuery->all(BaseActiveRecord::getDb());

		$response -> format = Response::FORMAT_JSON;

		$response -> data = $response;

		return $response;*/

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
	
	public function actionGetProjectUserTask()
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
	public function actionGetAllTask($filter = null, $listPerPage = 10, $page = 1){
        try{
            $responseArray = [];
            //set db target
            Task::setClient(BaseActiveController::urlPrefix());

            $userQuery = Task::find()
                ->select(['TaskID', 'TaskName', 'TaskQBReferenceID']);

            if($filter != null)
            {
                $userQuery->andFilterWhere([
                    'or',
                    ['like', 'TaskName', $filter],
                    ['like', 'TaskQBReferenceID', $filter],
                ]);
            }

            if($page != null)
            {
                //pass query with pagination data to helper method
                $paginationResponse = BaseActiveController::paginationProcessor($userQuery, $page, $listPerPage);
                //use updated query with pagination caluse to get data
                $data = $paginationResponse['Query']
                    ->orderBy(['TaskID'=>SORT_ASC, 'TaskName'=>SORT_ASC])
                    ->asArray()
                    ->all();
                $responseArray['pages'] = $paginationResponse['pages'];
                $responseArray['assets'] = $data;
            }

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