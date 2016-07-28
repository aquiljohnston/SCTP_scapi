<?php

namespace app\controllers;

use Yii;
use app\authentication\TokenAuth;
//use app\models\OQ;
use app\controllers\BaseActiveController;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;


/**
 * OQController creates user notifications.
 */
class OQController extends Controller
//swap when model is implemented
//class OqController extends BaseActiveController
{	
	
	//use when model is implemented
	//public $modelClass = 'app\models\OQ'; 
	
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
	
	public function actionGet($LANID)
	{
		//TODO RBAC permission check
		try{
			//TODO check headers
			
			//stub data
			$oqs = [];
			$oq1 = [];
			$oq2 = [];
			if($LANID == "M3D4")
			{
				$oq1["OQ"] = "0904- Leak Survey";
				$oq1["Status"] = "Current";
				$oq1["Expires"] = "1/1/2017";
				$oq2["OQ"] = "0906- XX Equipment";
				$oq2["Status"] = "Current";
				$oq2["Expires"] = "1/1/2017";
				$oqs[] = $oq1;
				$oqs[] = $oq2;
			}
			if($LANID == "A6V9")
			{
				$oq1["OQ"] = "0904- Leak Survey";
				$oq1["Status"] = "Lapsed";
				$oq1["Expires"] = "1/1/2016";
				$oq2["OQ"] = "0906- XX Equipment";
				$oq2["Status"] = "Lapsed";
				$oq2["Expires"] = "1/1/2016";
				$oqs[] = $oq1;
				$oqs[] = $oq2;
			}
			if($LANID == "T1Z3")
			{
				$oq1["OQ"] = "0904- Leak Survey";
				$oq1["Status"] = "Lapsed";
				$oq1["Expires"] = "1/1/2017";
				$oq2["OQ"] = "0906- XX Equipment";
				$oq2["Status"] = "Current";
				$oq2["Expires"] = "1/1/2016";
				$oqs[] = $oq1;
				$oqs[] = $oq2;
			}
			if($LANID == "J0P0")
			{
				$oq1["OQ"] = "0904- Leak Survey";
				$oq1["Status"] = "Current";
				$oq1["Expires"] = "1/1/2017";
				$oq2["OQ"] = "0906- XX Equipment";
				$oq2["Status"] = "Lapsed";
				$oq2["Expires"] = "1/1/2016";
				$oqs[] = $oq1;
				$oqs[] = $oq2;
			}
			
			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $oqs;
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