<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\Breadcrumb;
use app\modules\v1\controllers\BaseActiveController;
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
    public $modelClass = 'app\modules\v1\models\Breadcrumb'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	public function actionView($id)
	{
		try
		{
			//set db target
			Breadcrumb::setClient(BaseActiveController::urlPrefix());
			
			$breadcrumb = Breadcrumb::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $breadcrumb;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	use CreateMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
}