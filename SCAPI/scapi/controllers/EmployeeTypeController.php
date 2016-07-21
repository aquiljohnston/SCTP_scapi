<?php

namespace app\controllers;

use Yii;
use app\models\EmployeeType;
use app\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EmployeeTypeController implements the CRUD actions for EmployeeType model.
 */
class EmployeeTypeController extends BaseActiveController
{
	public $modelClass = 'app\models\EmployeeType'; 

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
	
	//return a json containing pairs of EmployeeTypes
	public function actionGetTypeDropdowns()
	{
		// RBAC permission check
		PermissionsController::requirePermission('employeeTypeGetDropdown');

		try
		{
			//set db target
			$headers = getallheaders();
			EmployeeType::setClient($headers['X-Client']);
		
			$types = EmployeeType::find()
				->all();
			$namePairs = [];
			$typesSize = count($types);
			
			for($i=0; $i < $typesSize; $i++)
			{
				$namePairs[$types[$i]->EmployeeTypeType]= $types[$i]->EmployeeTypeType;
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

	public function actionGetPgeDropdowns() {
		
		// TODO: Permissions check
		try {
			//TODO: headers and X-Client
			
			//TODO: Find EmployeeTypes
			$data = [
				"Employee",
				"Contractor",
				"Intern"
			];
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
			return $response;
		} catch (\Exception $e) {
			throw new BadRequestHttpException;
		}
	}
}