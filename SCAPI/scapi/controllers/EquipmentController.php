<?php

namespace app\controllers;

use Yii;
use app\models\Equipment;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class EquipmentController extends ActiveController
{
	public $modelClass = 'app\models\Equipment'; 
	public $equipment;
	
     /* public function actionIndex()
     {
         return $this->render('index');
     }
	 */
	 
	 public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
	
	 public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }
	
	  public function actionView($id)
    {
		$equipment = Equipment::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipment;
		
		return $response;
	} 
	
	//Not used now i
	/* public function actionUpdate()
    {
		$tempEquipment = Equipment::findOne(7);
		$tempEquipment['EquipmentProjectID'] = 1;
		$tempEquipment->save();
    } */
	
	/* public function actionDelete($id)
    {
        $equipment = Equipment::findOne($id);
		$equipment -> delete(); 
    }  */
	
	//return json array of all equipment for a project.
	public function actionViewEquipmentByProject($projectID)
	{
		//$criteria->select = new CDbCriteria();
		//$criteria->condition = "equipmentProject = $projectID";
		//$equipArray = Equipment::findAll($criteria);
		$equipArray = Equipment::findAll(['EquipmentProjectID'=>$projectID]);
		$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipData;
	}

	//return json array of all equipment.
	public function actionViewAll()
	{
		$equipArray = Equipment::find()->all();
		$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $equipData;
	}
}
