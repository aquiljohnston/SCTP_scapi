<?php

namespace app\controllers;

use Yii;
use app\models\TimeEntry;
use app\models\SCUser;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TimeEntryController implements the CRUD actions for TimeEntry model.
 */
class TimeEntryController extends BaseActiveController
{
    public $modelClass = 'app\models\TimeEntry'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
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
	
	public function actionView($id)
	{
		//set db target
		$headers = getallheaders();
		TimeEntry::setClient($headers['X-Client']);
		
		$timeEntry = TimeEntry::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timeEntry;
		
		return $response;
	}
	
	public function actionCreate()
	{
		//set db target
		$headers = getallheaders();
		TimeEntry::setClient($headers['X-Client']);
		SCUser::setClient($headers['X-Client']);
		
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new TimeEntry(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//removed to maintain data type
		// //created by
		// if ($user = SCUser::findOne(['UserID'=>$model->TimeEntryCreatedBy]))
		// {
			// $fname = $user->UserFirstName;
			// $lname = $user->UserLastName;
			// $model->TimeEntryCreatedBy = $lname.", ".$fname;
		// }
		
		//create date
		$model->TimeEntryCreateDate = date('Y-m-d H:i:s');
		
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
	
	public function actionGetEntriesByTimeCard($id)
	{
		//set db target
		$headers = getallheaders();
		TimeEntry::setClient($headers['X-Client']);
		
		$entriesArray = TimeEntry::findAll(['TimeEntryTimeCardID'=>$id]);
		$entryData = array_map(function ($model) {return $model->attributes;},$entriesArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $entryData;
	}
}