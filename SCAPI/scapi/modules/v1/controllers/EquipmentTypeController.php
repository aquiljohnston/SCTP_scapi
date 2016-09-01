<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\EquipmentType;
use app\modules\v1\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EquipmentTypeController implements the CRUD actions for EquipmentType model.
 */
class EquipmentTypeController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\EquipmentType'; 

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use CreateMethodNotAllowed;
	use ViewMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	//return a json containing pairs of EquipmentTypes
	public function actionGetTypeDropdowns()
	{		
		try
		{
			//set db target
			$headers = getallheaders();
			EquipmentType::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('equipmentTypeGetDropdown');
		
			$types = EquipmentType::find()
				->all();
			$namePairs = [];
			$typesSize = count($types);
			
			for($i=0; $i < $typesSize; $i++)
			{
				$namePairs[$types[$i]->EquipmentType]= $types[$i]->EquipmentType;
			}
				
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $namePairs;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}