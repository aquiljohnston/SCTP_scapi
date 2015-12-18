<?php

namespace app\controllers;

use Yii;
use app\models\Breadcrumb;
use app\authentication\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * BreadcrumbController implements the CRUD actions for the Breadcrumb model.
 */
class BreadcrumbController extends BaseActiveController
{
    public $modelClass = 'app\models\Breadcrumb'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
	
	public function actionView($id)
	{
		$breadcrumb = Breadcrumb::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $breadcrumb;
		
		return $response;
	}
	/* public function actionCreate()
	{
		$post = file_get_contents("php://input");
		$data = json_decode($post, true);

		$model = new Breadcrumb(); 
		$model->attributes = $data;  
		
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $data; 
		
		if($model-> save())
		{
			
			//$response->setStatusCode(201);
			return "michael Sucks! \n" . var_dump($model);
		} else {
			return $response;
		}
	} */
}