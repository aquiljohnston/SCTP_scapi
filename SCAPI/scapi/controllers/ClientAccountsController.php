<?php

namespace app\controllers;

use Yii;
use app\models\ClientAccounts;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class ClientAccountsController extends BaseActiveController
{
	public $modelClass = 'app\models\ClientAccounts'; 
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
	//return a json containing pairs of ClientAccountIDs and ClientNames
	public function actionGetClientAccountDropdowns()
	{	
		//set db target
		$headers = getallheaders();
		ClientAccounts::setClient($headers['X-Client']);
	
        $clientAccounts = ClientAccounts::find()
			->all();
		$namePairs = [];
		$clientSize = count($clientAccounts);
		
		for($i=0; $i < $clientSize; $i++)
		{
			$namePairs[$clientAccounts[$i]->ClientAccountNumber]= $clientAccounts[$i]->ClientAccountNumber . " - " . $clientAccounts[$i]->ClientAccountName;
		}
			
		
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $namePairs;
		
		return $response;
	}
}