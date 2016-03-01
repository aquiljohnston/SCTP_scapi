<?php

namespace app\controllers;

use Yii;
use app\models\ActivityCode;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ActivityCodeController implements the CRUD actions for ActivityCode model.
 */
class ActivityCodeController extends BaseActiveController
{
	public $modelClass = 'app\models\ActivityCode'; 

	
	
	//return a json containing pairs of EquipmentTypes
	public function actionGetCodeDropdowns()
	{	
        $codes = ActivityCode::find()
			->all();
		$namePairs = [];
		$codesSize = count($codes);
		
		for($i=0; $i < $codesSize; $i++)
		{
			$namePairs[$codes[$i]->ActivityCodeID]= $codes[$i]->ActivityCodeType;
		}
			
		
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $namePairs;
		
		return $response;
	}
}