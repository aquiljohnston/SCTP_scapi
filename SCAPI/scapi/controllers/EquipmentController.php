<?php

namespace app\controllers;

use Yii;
use app\models\Equipment;
use app\authentication\BaseActiveController;
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

	//return json array of all equipment for a project.
	public function actionViewEquipmentByProject($projectID)
	{
		//$criteria->select = new CDbCriteria();
		//$criteria->condition = "equipmentProject = $projectID";
		//$equipArray = Equipment::findAll($criteria);
		$equipArray = Equipment::findAll(['EquipmentProjectID'=>$projectID]);
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
}
