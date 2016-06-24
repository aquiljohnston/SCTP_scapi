<?php

namespace app\controllers;

use Yii;
use app\models\EquipmentStatus;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EquipmentStatusController implements the CRUD actions for EquipmentCondition model.
 */
class EquipmentStatusController extends BaseActiveController
{
	public $modelClass = 'app\models\EquipmentStatus'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use CreateMethodNotAllowed;
	use ViewMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	//return a json containing pairs of EquipmentConditions
	public function actionGetStatusDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			EquipmentStatus::setClient($headers['X-Client']);
			
			$status = EquipmentStatus::find()
				->all();
			$namePairs = [];
			$statusSize = count($status);
			
			for($i=0; $i < $statusSize; $i++)
			{
				$namePairs[$status[$i]->EquipmentStatusStatus]= $status[$i]->EquipmentStatusStatus;
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