<?php

namespace app\controllers;

use Yii;
use app\models\Equipment;
use app\models\Project;
use app\models\Client;
use app\models\SCUser;
use app\models\GetEquipmentByClientProjectVw;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class EquipmentController extends BaseActiveController
{
	public $modelClass = 'app\models\Equipment'; 
	public $equipment;
	 
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		return $actions;
	}
	
	public function actionView($id)
    {
		$equipment = Equipment::findOne($id);		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipment;
		
		return $response;
	} 
	
	public function actionCreate()
	{
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new Equipment(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//created by
		if ($user = SCUSer::findOne(['UserID'=>$model->EquipmentCreatedByUser]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->EquipmentCreatedByUser = $lname.", ".$fname;
		}
		
		//create date
		$model->EquipmentCreateDate = date('Y-m-d H:i:s');
		
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

		$model = Equipment::findOne($id);
		
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if ($user = SCUSer::findOne(['UserID'=>$model->EquipmentModifiedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->EquipmentModifiedBy = $lname.", ".$fname;
		}
		
		$model->EquipmentModifiedDate = date('Y-m-d H:i:s');
		
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

	//return json array of all equipment for a project.
	public function actionViewEquipmentByProject($projectID)
	{
		$equipArray = Equipment::findAll(['EquipmentProjectID'=>$projectID]);
		$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipData;
	}
	
	public function actionViewEquipmentByUser($userID)
	{
		$equipArray = Equipment::findAll(['EquipmentAssignedUserID'=>$userID]);
		$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipData;
	}

	//return json array of all equipment.
	public function actionViewAll()
	{
		$equipArray = Equipment::find()->all();
		$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipData;
	}
	
	//return db view for equipment index
	public function actionEquipmentView()
	{
		$equipArray = GetEquipmentByClientProjectVw::find()->all();
		$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipData;
	}
}
