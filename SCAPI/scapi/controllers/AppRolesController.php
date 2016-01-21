<?php

namespace app\controllers;

use Yii;
use app\models\AppRoles;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * AppRolesController implements the CRUD actions for AppRoles model.
 */
class AppRolesController extends BaseActiveController
{
	public $modelClass = 'app\models\AppRoles'; 

	
	
	//return a json containing pairs of AppRoleNames
	public function actionGetRolesDropdowns()
	{	
        $roles = AppRoles::find()
			->all();
		$namePairs = [];
		$rolesSize = count($roles);
		
		for($i=0; $i < $rolesSize; $i++)
		{
			$namePairs[$roles[$i]->AppRoleName]= $roles[$i]->AppRoleName;
		}
			
		
		$response = Yii::$app ->response;
		$response -> format = Response::FORMAT_JSON;
		$response -> data = $namePairs;
		
		return $response;
	}
}