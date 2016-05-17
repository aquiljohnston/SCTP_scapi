<?php

namespace app\controllers;

use Yii;
use app\models\Client;
use app\models\SCUser;
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
		unset($actions['update']);
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
	
	public function actionView($id)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
			
			$client = Client::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $client;
			
			return $response;
		}
		catch(ErrorException $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	
	public function actionCreate()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new Client(); 
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create date
			$model->ClientCreateDate = Parent::getDate();
			
			if($model-> save())
			{
				$response->setStatusCode(201);
				$response->data = $model; 
			}
			else
			{
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
			return $response;
		}
		catch(ErrorException $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionUpdate($id)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			$model = Client::findOne($id);
			
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->ClientModifiedDate = Parent::getDate();
			
			if($model-> update())
			{
				$response->setStatusCode(201);
				$response->data = $model; 
			}
			else
			{
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
			return $response;
		}
		catch(ErrorException $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	
	//return a json containing pairs of ClientID and ClientName
	public function actionGetClientDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			Client::setClient($headers['X-Client']);
		
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
		catch(ErrorException $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
