<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends ActiveController
{
	public $modelClass = 'app\models\User'; 
	
	public function actionView($id)
	{
		$model = $this->findModel($id);
		$arrayUser = (array) $model;
		return json_encode($arrayUser);
	}
}
