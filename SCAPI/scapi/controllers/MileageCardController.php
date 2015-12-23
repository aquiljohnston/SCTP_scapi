<?php

namespace app\controllers;

use Yii;
use app\models\MileageCard;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * MileageCardController implements the CRUD actions for MileageCard model.
 */
class MileageCardController extends BaseActiveController
{
    public $modelClass = 'app\models\MileageCard'; 
	public $mileageCard;
	 
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}

    /**
     * Displays a single MileageCard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $mileageCard = MileageCard::findOne($id);
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = $mileageCard;
		
		return $response;
    }

   
}
