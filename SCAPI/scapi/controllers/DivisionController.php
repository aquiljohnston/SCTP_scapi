<?php

namespace app\controllers;

use Yii;
use app\authentication\TokenAuth;
//use app\models\Division;
use app\controllers\BaseActiveController;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;


/**
 * DivisionController creates user notifications.
 */
class DivisionController extends Controller
//swap when model is implemented
//class DivisionController extends BaseActiveController
{	
	
	//use when model is implemented
	//public $modelClass = 'app\models\Division'; 
	
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
                    'get-dropdown' => ['get']
                ],  
            ];
		return $behaviors;		
	}
	
	public function actionGetDropdown()
	{
		//TODO RBAC permission check
		try{
			//TODO check headers
			
			//stub data
			$dropdown = [null => "Select..."];
			$dropdown["Belial"] = "Belial";
			$dropdown["Azmodan"] = "Azmodan";
			$dropdown["Diablo"] = "Diablo";
			$dropdown["Malthael"] = "Malthael";
			
			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $dropdown;
			return $response;
		}
		catch(ForbiddenHttpException $e)
		{
			throw new ForbiddenHttpException;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}