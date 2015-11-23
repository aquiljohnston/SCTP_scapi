<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Project;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\Link;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends ActiveController
{
	public $modelClass = 'app\models\User'; 
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
	
	public function actionView($id)
	{
		//$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
		$user = User::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $user;
		
		return $response;
	}

	public function actionAddUserToProject($userID,$projectID)
	{
		$user = User::findOne($userID);
		
		$project = Project::findOne($projectID);

		$user->link('projects',$project);
	}
	
	public function actionViewUsersByProject($projectID)
	{
		//$criteria->select = new CDbCriteria();
		//$criteria->condition = "equipmentProject = $projectID";
		//$equipArray = Equipment::findAll($criteria);
		//$userArray = User::findAll(['ProjUserProjectID'=>$projectID]);
		$project = Project::findOne($projectID);
		$userArray = $project->users;
		$userData = array_map(function ($model) {return $model->attributes;},$userArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $userData;
	}
}
