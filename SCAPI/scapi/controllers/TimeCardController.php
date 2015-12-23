<?php

namespace app\controllers;

use Yii;
use app\models\TimeCard;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * TimeCardController implements the CRUD actions for TimeCard model.
 */
class TimeCardController extends BaseActiveController
{
	public $modelClass = 'app\models\TimeCard';
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
	
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => TimeCard::find(),
        ]);
    }
	
    public function actionView($id)
    {
		$timeCard = TimeCard::findOne($id);
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $timeCard;
		return $response;
	}

   /*  public function actionCreate(id)
    {
		//Todo id is for the user
        $timeCard = new TimeCard();
		$dateToday = time();
		echo "Create TimeCard" ;
		
        $timeCard['TimeCardStartDate'] = $dateToday;
		
		$timeCard -> save();
    } */

   /*  public function actionDelete($id)
    {
        $timeCard = TimeCard::findOne($id);
		$timeCard -> delete(); 
    } */
	
	/* public function actionUpdate($id)
    {
		$timeCard = TimeCard::findOne($id);
		$timeCard['TimeCardProjectID'] = 1;
		$timeCard -> save();
    } */

    
    /* protected function findModel($id)
    {
        if (($model = TimeCard::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    } */
}
