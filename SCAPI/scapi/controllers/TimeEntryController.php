<?php

namespace app\controllers;

use Yii;
use app\models\TimeEntry;
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
		return $actions;
	}
	
	public function actionView($id)
	{
		$timeEntry = TimeEntry::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $timeEntry;
		
		return $response;
	}
	
	public function actionGetEntriesByTimeCard($id)
	{
		$entriesArray = TimeEntry::findAll(['TimeEntryTimeCardID'=>$id]);
		$entryData = array_map(function ($model) {return $model->attributes;},$entriesArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $entryData;
	}
}