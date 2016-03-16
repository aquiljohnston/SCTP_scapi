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

	
	
	//return a json containing pairs of EquipmentTypes
	public function actionGetTypeDropdowns()
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
}