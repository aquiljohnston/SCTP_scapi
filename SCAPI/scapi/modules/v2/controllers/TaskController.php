<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
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
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGetProjectTask($projectID)
	{
		

		$response = Yii::$app ->response;

		$taskQuery = new Query;

		$taskQuery->select('*')
					->from("vTaskAndProject(:projectID)")
					->addParams([':projectID' => $projectID]);

		$tasks = $taskQuery->all(BaseActiveRecord::getDb());

		$response -> format = Response::FORMAT_JSON;

		return $response;

		/*return [
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
		];*/
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
}