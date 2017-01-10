<?php

namespace app\modules\v1\modules\pge\controllers;

use yii;
use app\authentication\TokenAuth;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\controllers\MenuController;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

class PgeMenuController extends MenuController {
	
	const PERMISSIONS_CONTROLLER = 'app\modules\v1\modules\pge\controllers\PgePermissionsController';
	
	public function behaviors()
    {
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'get' => ['get']
                ],  
            ];
		return $behaviors;		
	}
	
	public function actionGet($project)
	{
		$headers = getallheaders();
		$parmArray = array($headers['X-Client']);
		return parent::actionGet($project, PgeMenuController::PERMISSIONS_CONTROLLER, $parmArray);
	}
}