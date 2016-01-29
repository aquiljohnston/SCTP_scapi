<?php

namespace app\controllers;

use Yii;
use app\models\MileageEntry;
use app\models\SCUser;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * MileageEntryController implements the CRUD actions for MileageEntry model.
 */
class MileageEntryController extends BaseActiveController
{
    public $modelClass = 'app\models\MileageEntry'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
	
	public function actionView($id)
	{
		//$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
		$mileage = MileageEntry::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $mileage;
		
		return $response;
	}
	
	public function actionCreate()
	{
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new MileageEntry(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		
		//created by
		if ($user = SCUser::findOne(['UserID'=>$model->MileageEntryCreatedBy]))
		{
			$fname = $user->UserFirstName;
			$lname = $user->UserLastName;
			$model->MileageEntryCreatedBy = $lname.", ".$fname;
		}
		
		//create date
		$model->MileageEntryCreateDate = date('Y-m-d H:i:s');
		
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
}
