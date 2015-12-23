<?php

namespace app\controllers;
use Yii;
use app\models\ProjectUser;
use yii\data\ActiveDataProvider;
use yii\controllers\BaseActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class ProjectUserController extends BaseActiveController
{
	public $modelClass = 'app\models\ProjectUser';
    public function actionIndex()
    {
        return $this->render('index');
    }
}
