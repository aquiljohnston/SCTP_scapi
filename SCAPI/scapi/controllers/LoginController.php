<?php

namespace app\controllers;

use Yii;
use app\models\SCUser;
use app\models\Auth;
use app\models\Key;
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
			try
			{
				$headers = getallheaders();
				SCUser::setClient($headers['X-Client']);
				Key::setClient($headers['X-Client']);
				Auth::setClient($headers['X-Client']);
			}
			catch(ErrorException $e)
			{
				throw new \yii\web\HttpException(400, 'Client Header Not Found.');
			}	
			
			
			//ic and secret key of openssl
			$iv = "abcdefghijklmnop";
			$secretKey= "sparusholdings12";
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//read the post input (use this technique if you have no post variable name):
			$post = file_get_contents("php://input");

			//decode json post input as php array:
			$data = json_decode($post, true);

			//login is a Yii model:
			$user = new SCUser();

			//load json data into model:
			$user->attributes = $data;  

			if($userName = SCUser::findOne(['UserName'=>$user->UserName, 'UserActiveFlag'=>1]))
				{
				$securedPass = $data["Password"];
				Yii::trace('securedPass: '.$securedPass);
				
				//Check password for authentication with try catch
				$decodedPass = base64_decode($securedPass);
				Yii::trace('decodedPass: '.$decodedPass);
				$decryptedPass = openssl_decrypt($decodedPass,  'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $iv);
				//$decryptedPass= Yii::$app->getSecurity()->decryptByPassword($decodedPass, $secretKey);
				Yii::trace('decryptedPass: '.$decryptedPass);
				$key = Key::findOne(['KeyID'=>$userName->UserKey]);
				$hash = $key->Key1;
				Yii::trace('Hash: '.$hash);
				//Check the Hash
				if (password_verify($decryptedPass, $hash)) 
				{
					Yii::trace('Password is valid.');
					
					//Pass
					Yii::$app->user->login($userName);
					//Generate Auth Token
					$auth = new Auth();
					$userID = $userName->UserID;
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
			//Fail
			//Send error
			
			//add auth token to response
			$response->data = $auth;
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	// User logout
	public function actionUserLogout($userID)
	{
		try
		{
			//set db target
			try
			{
				$headers = getallheaders();
				SCUser::setClient($headers['X-Client']);
			}
			catch(ErrorException $e)
			{
				throw new \yii\web\HttpException(400, 'Client Header Not Found.');
			}	
			
			$logoutString = "Logout Successful!";
			$response = Yii::$app->response;
			Yii::$app->user->logout($destroySession = true, $userID);
			$response->data = $logoutString;
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
}
