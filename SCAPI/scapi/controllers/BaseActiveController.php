<?php

namespace app\controllers;

use Yii;
use app\models\BaseActiveRecord;
use app\models\SCUser;
use app\authentication\TokenAuth;
use yii\db\ActiveRecord;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;

class BaseActiveController extends ActiveController
{	
	const DATE_FORMAT = 'Y-m-d H:i:s';
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['create']);
		return $actions;
	}
	
	/**
	* sets authenticator for token authentication
	* sets verb filters for http request
	* @return an array of behaviors
	*/
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

	/**
	* Creates a record for the sub class's model
	* @returns json body of the model data
	* @throws \yii\web\HttpException
	*/
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
	
	public function getDate()
	{
		return date(BaseActiveController::DATE_FORMAT);
	}	
	
	public static function getUserFromToken($token = null)
	{
		if ($token === null) {
			$token = Yii::$app->request->getAuthUser();
		}
		return SCUser::findIdentityByAccessToken($token);
	}

}