<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\EquipmentCalibration;
use app\modules\v1\controllers\BaseActiveController;
use app\authentication\TokenAuth;
// use app\modules\v1\authentication\TokenAuth;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\base\ErrorException;

class EquipmentCalibrationController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\EquipmentCalibration';
	
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
			EquipmentCalibration::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('equipmentCalibrationCreate');
			
			//format response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get post data
			$post = file_get_contents("php://input");
			$data = json_decode(utf8_decode($post), true);
			
			//pass data to model
			$model = new EquipmentCalibration(); 
			$model->attributes = $data;  
			$model->EquipmentCalibrationCreatedBy = self::getUserFromToken()->UserID;
			
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