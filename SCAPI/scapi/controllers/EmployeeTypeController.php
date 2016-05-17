<?php

namespace app\controllers;

use Yii;
use app\models\EmployeeType;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EmployeeTypeController implements the CRUD actions for EmployeeType model.
 */
class EmployeeTypeController extends BaseActiveController
{
	public $modelClass = 'app\models\EmployeeType'; 

	
	
	//return a json containing pairs of EmployeeTypes
	public function actionGetTypeDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			EmployeeType::setClient($headers['X-Client']);
		
			$types = EmployeeType::find()
				->all();
			$namePairs = [];
			$typesSize = count($types);
			
			for($i=0; $i < $typesSize; $i++)
			{
				$namePairs[$types[$i]->EmployeeTypeType]= $types[$i]->EmployeeTypeType;
			}
				
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $namePairs;
			
			return $response;
		}
		catch(ErrorException $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}
}