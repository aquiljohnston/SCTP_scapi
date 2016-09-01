<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use app\authentication\TokenAuth;
use app\modules\v1\modules\pge\models\WebManagementOQStatus;
use app\modules\v1\controllers\BaseActiveController;
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
//class OQController extends BaseActiveController
{	
	
	//use when model is implemented
	//public $modelClass = 'app\modules\v1\models\OQ'; 
	
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
		try{
			$headers = getallheaders();
			WebManagementOQStatus::setClient($headers['X-Client']);
			//TODO RBAC permission check
			
			$oqs = WebManagementOQStatus::find()
				->where(['UserLANID' => $LANID])
				->all();
			
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