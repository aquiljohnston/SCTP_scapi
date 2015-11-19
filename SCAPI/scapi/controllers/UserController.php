<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

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
		$user = User::findOne($id);
		//$arrayUser = (array) $user;
		//return json_encode($arrayUser);
		//return $model;
		//$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $user;
		
		return $response;
	}

    	
}
