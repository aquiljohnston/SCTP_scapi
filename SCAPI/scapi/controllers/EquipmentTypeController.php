<?php

namespace app\controllers;

use Yii;
use app\models\EquipmentType;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EquipmentTypeController implements the CRUD actions for EquipmentType model.
 */
class EquipmentTypeController extends BaseActiveController
{
	public $modelClass = 'app\models\EquipmentType'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionView($id)
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionCreate($id)
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionUpdate($id)
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	//return a json containing pairs of EquipmentTypes
	public function actionGetTypeDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			EquipmentType::setClient($headers['X-Client']);
		
			$types = EquipmentType::find()
				->all();
			$namePairs = [];
			$typesSize = count($types);
			
			for($i=0; $i < $typesSize; $i++)
			{
				$namePairs[$types[$i]->EquipmentType]= $types[$i]->EquipmentType;
			}
				
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $namePairs;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}