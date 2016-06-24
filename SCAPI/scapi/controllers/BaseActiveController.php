<?php

namespace app\controllers;

use Yii;
use app\models\BaseActiveRecord;
use app\authentication\TokenAuth;
use yii\db\ActiveRecord;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;

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
					'create' => ['post'],
					'update' => ['put'],
					'get-all' => ['get'],
                ],
            ];
		return $behaviors;
	}

	public function actionCreate()
    {
		try
		{
			//set model class
			$modelClass = $this->modelClass;
			
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			$modelClass::setClient($headers['X-Client']);
			
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
    }

	/**
	 * Gets all of the subclass's model's records
	 *
	 * @return Response The records in a JSON format
	 * @throws \yii\web\HttpException 400 if any exceptions are thrown
	 */
	public function actionGetAll()
	{
		try
		{
			//set model class
			$modelClass = $this->modelClass;
			
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			$modelClass::setClient($headers['X-Client']);
			
			$models = $modelClass::find()
				->all();
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $models;
			
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}

	/**
	 * Returns current date formatted in a standard way
	 *
	 * @return bool|string Formatted current date
	 */
	public function getDate()
	{
		return date('Y-m-d H:i:s');
	}
}