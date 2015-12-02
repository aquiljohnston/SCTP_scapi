<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Project;
use app\models\ProjectUser;
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

		$projUser = ProjectUser::find()
			->where(['and', "ProjUserUserID = $userID","ProjUserProjectID = $projectID"])
			->one();
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $projUser;
	}

}
