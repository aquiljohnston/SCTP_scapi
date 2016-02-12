<?php

namespace app\controllers;

use Yii;
use app\models\Project;
use app\models\SCUser;
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
		$project = Project::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $project;
		
		return $response;
	} 
	
	public function actionCreate()
	{
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
        $projects = Project::find()
			->all();
		$namePairs = [];
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
}
