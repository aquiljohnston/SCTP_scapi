<?php

namespace app\controllers;

use Yii;
use app\models\EquipmentCondition;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EquipmentConditionController implements the CRUD actions for EquipmentCondition model.
 */
class EquipmentConditionController extends BaseActiveController
{
	public $modelClass = 'app\models\EquipmentCondition'; 

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
	public function actionGetConditionDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			EquipmentCondition::setClient($headers['X-Client']);
			
			$condition = EquipmentCondition::find()
				->all();
			$namePairs = [];
			$conSize = count($condition);
			
			for($i=0; $i < $conSize; $i++)
			{
				$namePairs[$condition[$i]->EquipmentCondition]= $condition[$i]->EquipmentCondition;
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