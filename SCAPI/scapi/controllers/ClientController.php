<?php

namespace app\controllers;

use Yii;
use app\models\Client;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ClientController implements the CRUD actions for Client model.
 */
class ClientController extends BaseActiveController
{
	public $modelClass = 'app\models\Client'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
	
	public function actionView($id)
	{
		$client = Client::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $client;
		
		return $response;
	}
	
	public function actionGetClientDropdowns()
	{	
        $clients = Client::find()
			->all();
		$namePairs = [];
		$clientSize = count($clients);
		
		for($i=0; $i < $clientSize; $i++)
		{
			$namePairs[$clients[$i]->ClientID]= $clients[$i]->ClientName;
		}
			
		
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $namePairs;
		
		return $response;
	}
}
