<?php

namespace app\controllers;

use Yii;
use app\authentication\TokenAuth;
//use app\models\WorkCenter;
use app\controllers\BaseActiveController;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;


/**
 * WorkCenterController creates user notifications.
 */
class WorkCenterController extends Controller
//swap when model is implemented
//class WorkCenterController extends BaseActiveController
{	
	
	//use when model is implemented
	//public $modelClass = 'app\models\WorkCenter'; 
	
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
	
	public function actionGetDropdown($division = null)
	{
		//TODO RBAC permission check
		try{
			//TODO check headers
			
			//stub data
			$dropdown = [];
			if($division == null)
			{
				$dropdown[] = "Zoltun Kulle";
				$dropdown[] = "Cydaea";
				$dropdown[] = "Izual";
				$dropdown[] = "Urzael";
			}
			elseif ($division == "Belial")
			{
				$dropdown[] = "Zoltun Kulle";
			}
			elseif ($division == "Azmodan")
			{
				$dropdown[] = "Cydaea";
			}
			elseif ($division == "Diablo")
			{
				$dropdown[] = "Izual";
			}
			elseif ($division == "Malthael")
			{
				$dropdown[] = "Urzael";
			}
			
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