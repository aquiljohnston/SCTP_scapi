<?php

namespace app\controllers;

use Yii;
use app\models\EquipmentCalibration;
use app\controllers\BaseActiveController;
use app\authentication\TokenAuth;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\base\ErrorException;

class EquipmentCalibrationController extends BaseActiveController
{
	public $modelClass = 'app\models\EquipmentCalibration';
	
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
					'create'  => ['post'],
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
	
	use ViewMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	//Create a new equipment calibration record
	public function actionCreate()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			EquipmentCalibration::setClient($headers['X-Client']);
			
			//format response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			
			//pass data to model
			$model = new EquipmentCalibration(); 
			$model->attributes = $data;  
			
			//create date
			$model->EquipmentCalibrationCreateDate = Parent::getDate();
			
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
	
}