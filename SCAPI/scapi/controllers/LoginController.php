<?php

namespace app\controllers;

use Yii;
use app\models\Login;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\helpers\Json;

class LoginController extends ActiveController
{
	public $modelClass = 'app\models\Login'; 
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
	
	 public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
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
		//read the post input (use this technique if you have no post variable name):
		$post = file_get_contents("php://input");

		//decode json post input as php array:
		$data = json_decode($post, true);

		//login is a Yii model:
		$login = new Login();

		//load json data into model:
		$login->attributes = $data;  
		$userloginID = Login::findOne(['UserLoginID'=>$login->UserLoginID]);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $userloginID;
		return $response;
	}
	
	// the current user identity. Null if the user is not authenticated.
	$identity = Yii::$app->user->identity;

	// the ID of the current user. Null if the user not authenticated.
	$id = Yii::$app->user->id;

	// whether the current user is a guest (not authenticated)
	$isGuest = Yii::$app->user->isGuest;
	
	// find a user identity with the specified username.
	// note that you may want to check the password if needed
	$identity = User::findOne(['username' => $username]);

	// logs in the user 
	Yii::$app->user->login($identity);
	
	// User logout
	public function actionUserLogout(){
		Yii::$app->user->logout();
		$response = Yii::$app->response;
		$response->data = 'Logout Successfully !';
		return $response;
	}
	
}
