<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\ActivityCode;
use app\modules\v1\controllers\BaseActiveController;
use app\authentication\TokenAuth;
// use app\modules\v1\authentication\TokenAuth;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ActivityCodeController implements the CRUD actions for ActivityCode model.
 */
class ActivityCodeController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\ActivityCode';

	/**
	 * Activates VerbFilter behaviour
	 * See documentation on behaviours at http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html
	 * @return array An array containing behaviours
	 */
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'get-code-dropdowns'  => ['get'],
                ],  
            ];
		return $behaviors;	
	}

	/**
	 * Unsets the default actions to prevent a security hole.
	 *
	 * @return array An array containing the parent's actions with some removed
	 */
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}

	use ViewMethodNotAllowed;
	use CreateMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;

	/**
	 * Assembles all ActivityCodeTypes in an array with ActivityCodeID keys and returns them in a response
	 *
	 * @return Response A JSON array containing pairs of ActivityCodes
	 * @throws \yii\web\HttpException
	 */
	public function actionGetCodeDropdowns()
	{
		// RBAC permission check
		PermissionsController::requirePermission('activityCodeGetDropdown');

		try
		{
			//set db target
			$headers = getallheaders();
			ActivityCode::setClient($headers['X-Client']);
		
			$codes = ActivityCode::find()
				->all();
			$namePairs = [];
			$codesSize = count($codes);
			
			for($i=0; $i < $codesSize; $i++)
			{
				$namePairs[$codes[$i]->ActivityCodeID]= $codes[$i]->ActivityCodeType;
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