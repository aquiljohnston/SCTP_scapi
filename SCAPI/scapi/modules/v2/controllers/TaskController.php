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
	
	public function actionGetProjectTask()
	{
		//TODO add call to db
		return [
			'1' => 'Training',
			'2' => 'Leak Survey',
			'3' => 'Dropping Kids At The Pool',
		];
	}
	
	public function actionGetProjectUserTask()
	{
		//TODO add call to db
		return [
			'1' => 'Training',
			'2' => 'Leak Survey',
		];
	}
}