<?php

namespace app\controllers;

use Yii;
use app\models\SCUser;
use app\models\Project;
use app\models\ProjectUser;
use app\models\Key;
use app\models\JobCode;
use app\models\Equipment;
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
		unset($actions['update']);
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
		$model = new SCUser();
		$model->attributes = $data;  
		
		//created by
		if ($user = SCUSer::findOne(['UserID'=>$model->UserCreatedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->UserCreatedBy = $lname.", ".$fname;
		}
		
		//created date
		$model->UserCreatedDate = date('Y-m-d H:i:s');
		
		if($model-> save())
		{
			$response->setStatusCode(201);
			$response->data = $model;
		}
		else
		{
			$response->setStatusCode(400);
			$response->data = "Http:400 Bad Request";
		}
		return $response;
	}
	
	public function actionUpdate($id)
	{
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);
		
		//get model to be updated
		$model = SCUSer::findOne($id);
		
		//iv and key for openssl
		$iv = "abcdefghijklmnop";
		$sKey ="sparusholdings12";
		
		//options for bcrypt
		$options = [
			'cost' => 12,
		];
		//handle the password
		//get pass from data
		$securedPass = $data["UserKey"];
		//decode the base 64 encoding
		$decodedPass = base64_decode($securedPass);
		//decrypt with openssl using the key and iv
		$decryptedPass = openssl_decrypt($decodedPass,  'AES-128-CBC', $sKey, OPENSSL_RAW_DATA, $iv);
		
		//check if new passowrd
		if($decryptedPass != $model->UserKey)
		{
			//hash pass with bcrypt
			$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
			
			//create row in the db to hold the hashedPass
			$keyData = Key::findOne($model->UserKey);
			$keyData->Key1 = $hashedPass;
			$keyData-> update();
			//Replace the encoded pass with the ID for the new KeyTb row
			$data["UserKey"] = $keyData -> KeyID;
		}
		else
		{
			$data["UserKey"] = $decryptedPass;
		}
		
		//pass new data to model
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if ($user = SCUSer::findOne(['UserID'=>$model->UserModifiedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->UserModifiedBy = $lname.", ".$fname;
		}
		
		$model->UserModifiedDate = date('Y-m-d H:i:s');
		
		if($model-> update())
		{
			$response->setStatusCode(201);
			$response->data = $model; 
		}
		else
		{
			$response->setStatusCode(400);
			$response->data = "Http:400 Bad Request";
		}
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
	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	// public function actionDelete($id)
	// {
		// //may need to add a try catch here in case of no content
		// //create response
		// $response = Yii::$app->response;
		// $response ->format = Response::FORMAT_JSON;
		// //find user model
		// $user = SCUser::findOne($id);
		// //find associated key model
		// $key = Key::findOne($user->UserKey);
		// //delete user and key in that order
		// if($user->delete() && $key->delete())
		// {
			// $response->setStatusCode(204);
		// }
		// //response data
		// return $response;
	// }

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
	
	public function actionGetMe($userID)
	{
		//get user
		$user = SCUser::findOne($userID);
		
		//get equipment for user
		$equipment = Equipment::find()
			->where("EquipmentAssignedUserID = $userID")
			->all();
		
		//get users realtionship to projects
		$projectUser = ProjectUser::find()
			->where("ProjUserUserID = $userID")
			->all();

		//get projects based on relationship
		$projectUserLength = count($projectUser);
		$projects  = [];
		for($i=0; $i < $projectUserLength; $i++)
		{
			//get job codes for project, for now just getting all job codes
			$jobCodes = JobCode::find()
			->all();
			$jobCodesArray = array_map(function ($model) {return $model->attributes;},$jobCodes);
			$jobCodesLength = count($jobCodesArray);
			for($j=0; $j < $jobCodesLength; $j++)
			{
				//get payroll code
				$jobCodesArray[$j]["PayrollCode"] = "TODO";
			}
			$projectID = $projectUser[0]->ProjUserProjectID ;
			$projectModel = Project::findOne($projectID);
			$projectData["ProjectID"]= $projectModel->ProjectID;  
			$projectData["ProjectName"]= $projectModel->ProjectName;  
			$projectData["ProjectClientID"]= $projectModel->ProjectClientID;  
			$projectData["JobCodes"]= $jobCodesArray; 
			$projects[] = $projectData;
		}
		
		//load data into array
		$dataArray = [];
		$dataArray["User"] = $user;
		$dataArray["Projects"] = $projects;
		$dataArray["Equipment"] = $equipment;
		
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $dataArray;
	}
}
