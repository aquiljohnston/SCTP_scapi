<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\ClientAccounts;
use app\modules\v1\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class ClientAccountsController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\ClientAccounts'; 
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use ViewMethodNotAllowed;
	use CreateMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	//return a json containing pairs of ClientAccountIDs and ClientNames
	public function actionGetClientAccountDropdowns()
	{		
		try
		{
			//set db target
			ClientAccounts::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('clientAccountsGetDropdown');
		
			$clientAccounts = ClientAccounts::find()
				->all();
			$namePairs = [null => "Unassigned"];
			$clientSize = count($clientAccounts);
			
			for($i=0; $i < $clientSize; $i++)
			{
				$namePairs[$clientAccounts[$i]->ClientAccountNumber]= $clientAccounts[$i]->ClientAccountNumber . " - " . $clientAccounts[$i]->ClientAccountName;
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