<?php

namespace app\controllers;

use Yii;
use app\models\SCUser;
use app\models\Project;
use app\models\ProjectUser;
use app\models\Key;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\Link;


/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends BaseActiveController
{
	public $modelClass = 'app\models\SCUser'; 
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionCreate()
	{
		//create response
		$response = Yii::$app->response;
		//iv and key for openssl
		$iv = "abcdefghijklmnop";
		$sKey ="sparusholdings12";
		//options for bcrypt
		$options = [
			'cost' => 12,
		];
		//read the post input (use this technique if you have no post variable name):
		$post = file_get_contents("php://input");
		//decode json post input as php array:
		$data = json_decode($post, true);
		
		//handle the password
		//get pass from data
		$securedPass = $data["UserKey"];
		//decode the base 64 encoding
		$decodedPass = base64_decode($securedPass);
		//decrypt with openssl using the key and iv
		$decryptedPass = openssl_decrypt($decodedPass,  'AES-128-CBC', $sKey, OPENSSL_RAW_DATA, $iv);
		Yii::trace('decryptedPass: '.$decryptedPass);
		//hash pass with bcrypt
		$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
		
		//create row in the db to hold the hashedPass
		$keyData = new Key();
		$keyData->Key1 = $hashedPass;
		$keyData-> save();
		
		//Replace the encoded pass with the ID for the new KeyTb row
		$data["UserKey"] = $keyData -> KeyID;
		
		//maps the data to a new user model and save
		$user = new SCUser();
		$user->attributes = $data;  
		if($user-> save())
		{
			$response->setStatusCode(201);
		}
		
		//response json
		$response->data = $user;
		return $response;
	}
	
	public function actionView($id)
	{
		//$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
		$user = SCUser::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $user;
		
		return $response;
	}

	public function actionAddUserToProject($userID,$projectID)
	{
		$user = SCUser::findOne($userID);
		
		$project = Project::findOne($projectID);

		$user->link('projects',$project);

		$projUser = ProjectUser::find()
			->where(['and', "ProjUserUserID = $userID","ProjUserProjectID = $projectID"])
			->one();
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $projUser;
	}
	
	public function actionDelete($id)
	{
		//may need to add a try catch here in case of no content
		//create response
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		//find user model
		$user = SCUser::findOne($id);
		//find associated key model
		$key = Key::findOne($user->UserKey);
		//delete user and key in that order
		if($user->delete() && $key->delete())
		{
			$response->setStatusCode(204);
		}
		//response data
		return $response;
	}

	public function actionGetUserDropdowns()
	{	
        $users = SCUser::find()
			->all();
		$namePairs = [];
		$userSize = count($users);
		
		for($i=0; $i < $userSize; $i++)
		{
			$namePairs[$users[$i]->UserID]= $users[$i]->UserLastName. ", ". $users[$i]->UserFirstName;
		}
			
		
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $namePairs;
		
		return $response;
	}
	
}
