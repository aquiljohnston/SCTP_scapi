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

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'deactivate' => ['put'],
                ],  
            ];
		return $behaviors;	
	}
	
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
		try
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
		catch(\Exception $e)  
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
			TimeEntry::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new TimeEntry(); 
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create date
			$model->TimeEntryCreateDate = Parent::getDate();
			
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
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionGetEntriesByTimeCard($id)
	{
		try
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
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionDeactivate()
	{
		try{
			//set db target
			$headers = getallheaders();
			TimeEntry::setClient($headers['X-Client']);
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//parse json
			$deactivatedBy = $data["deactivatedBy"];
			$entryIDs = $data["entryArray"];
			
			//get mileage entries
			foreach($entryIDs as $id)
			{
				$approvedEntries[]= TimeEntry::findOne($id);
			}
			
			try
			{
				//create transaction
				$connection = \Yii::$app->db;
				$transaction = $connection->beginTransaction(); 
			
				foreach($approvedEntries as $entry)
				{
					$entry-> TimeEntryActiveFlag = "Inactive";
					$entry-> TimeEntryModifiedDate = Parent::getDate();
					$entry-> TimeEntryModifiedBy = $deactivatedBy;
					$entry-> update();
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $approvedEntries; 
				return $response;
			}
			//if transaction fails rollback changes and send error
			catch(Exception $e)
			{
				$transaction->rollBack();
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				return $response;
			}
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}