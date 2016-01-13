<?php

namespace app\controllers;

use Yii;
use app\models\SCUser;
use app\models\Auth;
use app\models\Key;
use app\authentication\CTUser;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\helpers\Json;
use yii\base\ErrorException;

class LoginController extends ActiveController
{
	public $modelClass = 'app\models\SCUser'; 
	public $Login;
	
     /* public function actionIndex()
     {
         return $this->render('index');
     }
	 */
	 
	 public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
		
	  public function actionView($Username)
    {
		$Login = Login::findOne($Username);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $login;
		
		return $response;
	} 
	
	public function actionUserLogin()
	{
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
		$userName = SCUser::findOne(['UserName'=>$user->UserName]);
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
			$auth-> beforeSave(true);
			//Store Auth Token
			$auth-> save();
		} else 
		{
			$response->data = "Password is invalid.";
			return $response;
			Yii::trace('Password is invalid.');
		}
		//Fail
		//Send error
		
		//add auth token to response
		$response->data = $auth;
		return $response;
	}
	
	// // the current user identity. Null if the user is not authenticated.
	// $identity = Yii::$app->user->identity;

	// // the ID of the current user. Null if the user not authenticated.
	// $id = Yii::$app->user->id;

	// // whether the current user is a guest (not authenticated)
	// $isGuest = Yii::$app->user->isGuest;
	
	// // find a user identity with the specified username.
	// // note that you may want to check the password if needed
	// $identity = User::findOne(['username' => $username]);

	// // logs in the user 
	// Yii::$app->user->login($identity);
	
	// User logout
	public function actionUserLogout($userID)
	{
		Yii::trace('Logout has been called');
		$response = Yii::$app->response;
		Yii::$app->user->logout($destroySession = true, $userID);
		$response->data = 'Logout Successful!';
		return $response;
	}
	
}
