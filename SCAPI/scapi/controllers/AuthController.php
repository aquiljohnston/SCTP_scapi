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
	 
	public function actionGetUserByToken($token)
    {
		$auth = Auth::findOne(['AuthToken'=>$token]);
		$userID = $auth->AuthUserID;
		$user = SCUser::findOne($userID);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $user;
		
		return $response;
	} 
	
	public function actionValidateAuthKey($token)
	{
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
}
