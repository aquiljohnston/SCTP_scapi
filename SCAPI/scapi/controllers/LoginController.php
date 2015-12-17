<?php

namespace app\controllers;

use Yii;
use app\models\SCUser;
use app\models\Auth;
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
		$iv = "abcdefghijklmnop";
		$secretKey= "sparus";
		$testHash= '$2y$12$yYwybpR.JabbAOKTx6/I1uZgSg4lJZ5x12RT33I4LYF8cqG1/V5qC';
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
		//mcrypt_decrypt ( string $cipher , string $key , string $data , string $mode [, string $iv ] )
		//$decryptedPass = mcrypt_decrypt ( MCRYPT_RIJNDAEL_128 , "sparusholdings12" , $decodedPass , MCRYPT_MODE_CBC, "abcdefghijklmnop");
		//$decryptedPass = mcrypt_decrypt ( MCRYPT_RIJNDAEL_128 , "sparusholdings12" , $securedPass , MCRYPT_MODE_CBC, "abcdefghijklmnop");
		$decryptedPass = openssl_decrypt($decodedPass,  'AES-256-CBC', "sparusholdings12", OPENSSL_RAW_DATA, $iv);
		//$decryptedPass= Yii::$app->getSecurity()->decryptByPassword($decodedPass, $secretKey);
		Yii::trace('decryptedPass: '.$decryptedPass);
		//Check the Hash
		if (password_verify($decryptedPass, $testHash)) {
			Yii::trace('Password is valid.');
		} else {
			Yii::trace('Password is invalid.');
		}
		
		
		//Pass
		Yii::$app->user->login($userName);
		//Generate Auth Token
		$auth = new Auth();
        $userID = $userName->UserID;
		$auth->UserID = $userID;
		$auth-> beforeSave(true);
		//Store Auth Token
		$auth-> save();
		
		//Fail
		//Send error
		
		//add auth token to response
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
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
	public function actionUserLogout()
	{
		Yii::trace('Logout has been called');
		$response = Yii::$app->response;
		Yii::$app->user->logout();
		$response->data = 'Logout Successful!';
		return $response;
	}
	
}
