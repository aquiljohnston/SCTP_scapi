<?php

namespace app\controllers;

use Yii;
use app\models\StateCode;
use app\controllers\BaseActiveController;
use app\authentication\TokenAuth;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ActivityCodeController implements the CRUD actions for StateCode model.
 */
class StateCodeController extends BaseActiveController
{
	public $modelClass = 'app\models\ActivityCode'; 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'get-code-dropdowns'  => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionView()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionCreate()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	public function actionUpdate()
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
			StateCode::setClient($headers['X-Client']);
		
			$codes = StateCode::find()
				->all();
			$namePairs = [null => "None"];
			$tempPairs = [];
			$codesSize = count($codes);
			
			for($i=0; $i < $codesSize; $i++)
			{
				$namePairs[$codes[$i]->StateNames]= $codes[$i]->StateNumber . ": " . $codes[$i]->StateNames ;
			}
			$namePairs = $namePairs + $tempPairs;				
			
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