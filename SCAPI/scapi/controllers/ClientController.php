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
		$client = Client::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $client;
		
		return $response;
	}
	
	
	public function actionCreate()
	{
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new Client(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//created by
		if ($user = SCUSer::findOne(['UserID'=>$model->ClientCreatorUserID]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->ClientCreatorUserID = $lname.", ".$fname;
		}
		
		//create date
		$model->ClientCreateDate = date('Y-m-d H:i:s');
		
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
	
	public function actionUpdate($id)
	{
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);

		$model = Client::findOne($id);
		
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		if ($user = SCUSer::findOne(['UserID'=>$model->ClientModifiedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->ClientModifiedBy = $lname.", ".$fname;
		}
		
		$model->ClientModifiedDate = date('Y-m-d H:i:s');
		
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
	
	
	//return a json containing pairs of ClientID and ClientName
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
