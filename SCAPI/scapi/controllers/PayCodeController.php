<?php

namespace app\controllers;

use Yii;
use app\models\PayCode;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PayCodeController implements the CRUD actions for PayCode model.
 */
class PayCodeController extends BaseActiveController
{
	public $modelClass = 'app\models\PayCode'; 

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
	public function actionGetCodeDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			PayCode::setClient($headers['X-Client']);
		
			$codes = PayCode::find()
				->all();
			$namePairs = [];
			$codesSize = count($codes);
			
			for($i=0; $i < $codesSize; $i++)
			{
				$namePairs[$codes[$i]->PayCodeID]= $codes[$i]->PayCodeType;
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