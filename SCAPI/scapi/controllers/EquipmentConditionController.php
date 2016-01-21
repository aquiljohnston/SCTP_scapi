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

	
	
	//return a json containing pairs of EquipmentConditions
	public function actionGetConditionDropdowns()
	{	
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
}