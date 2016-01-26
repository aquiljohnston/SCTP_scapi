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
		return $actions;
	}

	public function actionView($id)
    {
		$project = Project::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $project;
		
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
