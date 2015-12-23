<?php

namespace app\controllers;

use Yii;
use yii\db\ActiveRecord;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class BaseActiveController extends ActiveController
{
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['create']);
		return $actions;
	}
	
   public function behaviors()
    {
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['delete'],
                ],  
            ];
		return $behaviors;		
	}
	
	
	public function actionCreate()
    {
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new $this->modelClass(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $model; 
		
		if($model-> save())
		{
			$response->setStatusCode(201);
			return $response;
		}
		else
		{
			$response->setStatusCode(400);
			$response->data = "Http:400 Bad Request";
		}
		
		
	  

    }
}