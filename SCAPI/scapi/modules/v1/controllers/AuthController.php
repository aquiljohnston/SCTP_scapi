<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\Auth;
use app\modules\v1\models\SCUSer;
use app\modules\v1\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class AuthController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\Auth'; 
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}

	// See traits subdirectory
	use CreateMethodNotAllowed;
	use ViewMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;


	/**
	 * Finds a User using a provided token.
	 *
	 * This is done by finding the Auth that matches the token
	 * then the User that matches the Auth's User ID.
	 *
	 * @param $token string The token to find the user with
	 * @return Response A JSON representation of the User.
	 * @throws \yii\web\HttpException
	 */
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
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}

	/**
	 * Verifies provided AuthToken against Auth
	 * 
	 * @param $token
	 * @return Response Contains true or false depending on the verification.
	 * @throws \yii\web\HttpException Throws 400 upon any Exception being thrown.
	 */
	public function actionValidateAuthKey($token)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			Auth::setClient($headers['X-Client']);
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
