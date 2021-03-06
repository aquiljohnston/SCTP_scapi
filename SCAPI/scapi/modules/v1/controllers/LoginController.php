<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\Auth;
use app\modules\v1\controllers\BaseActiveController;
use app\authentication\CTUser;
use yii\data\ActiveDataProvider;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\helpers\Json;
use yii\base\ErrorException;

class LoginController extends Controller
{
		
	public function actionUserLogin()
	{
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//read the post input (use this technique if you have no post variable name):
			$post = file_get_contents("php://input");

			//decode json post input as php array:
			$data = json_decode($post, true);

			//login is a Yii model:
			$userName = new SCUser();

			//load json data into model:
			$userName->UserName = $data['UserName'];  

			if($user = SCUser::findOne(['UserName'=>$userName->UserName, 'UserActiveFlag'=>1]))
				{
				$securedPass = $data["Password"];
				
				//decrypt password
				$decryptedPass = BaseActiveController::decrypt($securedPass);

				$hash = $user->UserPassword;
				Yii::trace('Hash: '.$hash);
				//Check the Hash
				if (password_verify($decryptedPass, $hash)) 
				{
					Yii::trace('Password is valid.');
					
					//Pass
					Yii::$app->user->login($user);
					//Generate Auth Token
					$auth = new Auth();
					$userID = $user->UserID;
					$auth->AuthUserID = $userID;
					$auth->AuthCreatedBy = $userID;
					$auth-> beforeSave(true);
					//Store Auth Token
					$auth-> save();
				}
				else
				{
					$response->data = "Password is invalid.";
					$response->setStatusCode(401);
					return $response;
					Yii::trace('Password is invalid.');
				}
			}
			else
			{
				$response->data = "User not found or inactive.";
				$response->setStatusCode(401);
				return $response;
			}
			
			$authArray = ArrayHelper::toArray($auth);
			$authArray['UserFirstName'] = $user->UserFirstName;
			$authArray['UserLastName'] = $user->UserLastName;
			$authArray['UserUID'] = $user->UserUID;
			
			//add auth token to response
			$response->data = $authArray;
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	// User logout
	public function actionUserLogout()
	{
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			$logoutString = "Logout Successful!";
			$response = Yii::$app->response;
			Yii::$app->user->logout($destroySession = true);
			$response->data = $logoutString;
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
}
