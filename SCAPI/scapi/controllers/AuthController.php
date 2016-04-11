<?php

namespace app\controllers;

use Yii;
use app\models\Auth;
use app\models\SCUSer;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class AuthController extends BaseActiveController
{
	public $modelClass = 'app\models\Auth'; 
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionView($id)
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionCreate($id)
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionUpdate($id)
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionDelete($id)
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionGetUserByToken($token)
    {
		try
		{
			//set db target
			$headers = getallheaders();
			Auth::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$auth = Auth::findOne(['AuthToken'=>$token]);
			$userID = $auth->AuthUserID;
			$user = SCUser::findOne($userID);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $user;
			
			return $response;
		}
		catch(ErrorException $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	} 
	
	public function actionValidateAuthKey($token)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			Auth::setClient($headers['X-Client']);
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			if($auth = Auth::findOne(['AuthToken'=>$token]))
			{
				$response->data = true;
			}
			else
			{
				$response->data = false;
			}
			
			return $response;
		}
		catch(ErrorException $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
