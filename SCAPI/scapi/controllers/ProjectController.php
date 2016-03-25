<?php

namespace app\controllers;

use Yii;
use app\models\Project;
use app\models\SCUser;
use app\models\ProjectUser;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\Link;
use yii\filters\auth\TokenAuth;


/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends BaseActiveController
{
	public $modelClass = 'app\models\Project'; 
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'view-all-users'  => ['get'],
					'get-project-dropdowns'  => ['get'],
					'get-user-relationships'  => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}

	public function actionView($id)
    {
		//set db target
		$headers = getallheaders();
		Project::setClient($headers['X-Client']);
		
		$project = Project::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $project;
		
		return $response;
	} 
	
	public function actionCreate()
	{
		//set db target
		$headers = getallheaders();
		Project::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new Project(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//created by
		if ($user = SCUSer::findOne(['UserID'=>$model->ProjectCreatedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->ProjectCreatedBy = $lname.", ".$fname;
		}
		
		//create date
		$model->ProjectCreateDate = date('Y-m-d H:i:s');
		
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
		//set db target
		$headers = getallheaders();
		Project::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);

		$model = Project::findOne($id);
		
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if ($user = SCUSer::findOne(['UserID'=>$model->ProjectModifiedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->ProjectModifiedBy = $lname.", ".$fname;
		}
		
		$model->ProjectModifiedDate = date('Y-m-d H:i:s');
		
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
	
	//return json array of all users attached to a specific project ID
	public function actionViewAllUsers($projectID)
	{
		//set db target
		$headers = getallheaders();
		Project::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		ProjectUser::setClient($headers['X-Client']);
		
		$project = Project::findOne($projectID);
		$userArray = $project->users;
		$userData = array_map(function ($model) {return $model->attributes;},$userArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $userData;
	}
	
	//return a json containing pairs of ProjectID and ProjectName
	public function actionGetProjectDropdowns()
	{	
		//set db target
		$headers = getallheaders();
		Project::setClient($headers['X-Client']);
	
        $projects = Project::find()
			->all();
		$namePairs = [null => "Unassigned"];
		$projectSize = count($projects);
		
		for($i=0; $i < $projectSize; $i++)
		{
			$namePairs[$projects[$i]->ProjectID]= $projects[$i]->ProjectName;
		}
			
		
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $namePairs;
		
		return $response;
	}
	
	//returns json containing two arrays one of all users associated with a project the other of all users not associated with a project 
	public function actionGetUserRelationships($projectID)
	{
		//set db target
		$headers = getallheaders();
		SCUser::setClient($headers['X-Client']);
		Project::setClient($headers['X-Client']);
		ProjectUser::setClient($headers['X-Client']);
		
		//get all users for the project
		$project = Project::findOne($projectID);
		$includedUsers = $project->users;
		$includedPairs = [];
		$includedSize = count($includedUsers);
		
		//create array of included user id/name pairs
		for($i=0; $i < $includedSize; $i++)
		{
			$includedPairs[$includedUsers[$i]->UserID]= $includedUsers[$i]->UserLastName. ", ". $includedUsers[$i]->UserFirstName;
		}
		
		//get all users
		$allUsers = SCUser::find()
			->all();
		
		$excludedPairs = [];
		$excludedSize = count($allUsers);
		
		//create array of all user id/name pairs
		for($i=0; $i < $excludedSize; $i++)
		{
			$excludedPairs[$allUsers[$i]->UserID]= $allUsers[$i]->UserLastName. ", ". $allUsers[$i]->UserFirstName;
		}
		
		//filter included pairs
		foreach($excludedPairs as $ek => $ev)
		{
			foreach($includedPairs as $ik => $iv)
			{
				if($ek == $ik)
				{
					unset($excludedPairs[$ek]);
				}
			}
		}
		
		//build response json
		$data = [];
		$data["excludedUsers"] = $excludedPairs;
		$data["includedUsers"] = $includedPairs; 
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $data;
	}
}
