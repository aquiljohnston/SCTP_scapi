<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\AppRoles;
use app\authentication\TokenAuth;
// use app\modules\v1\authentication\TokenAuth;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * AppRolesController implements the CRUD actions for AppRoles model.
 */
class AppRolesController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\AppRoles'; 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'get-roles-dropdowns'  => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
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
	
	//return
	/**
	 * Route to get the dropdown
	 * 
	 * The pairing of equal Strings for both key and value is done because the front end expects
	 * an associative array. We use the display name as the key for convenience.
	 *
	 * @return Response A JSON associative array containing pairs of AppRoleNames
	 * @throws \yii\web\HttpException
	 */
	public function actionGetRolesDropdowns()
	{
		try
		{
			//set db target
			AppRoles::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('appRoleGetDropdown');
		
			$roles = AppRoles::find()
				->all();
			$namePairs = [];
			$rolesSize = count($roles);
			
			for($i=0; $i < $rolesSize; $i++)
			{
				$namePairs[$roles[$i]->AppRoleName]= $roles[$i]->AppRoleName;
			}
			
			if (!PermissionsController::can('userCreateAdmin'))
			{
				unset($namePairs['Admin']);
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