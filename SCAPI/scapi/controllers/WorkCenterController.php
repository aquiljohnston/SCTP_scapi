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
	
	//required format for the dynaic dropdowns 
	//['id'=>'<sub-cat_id_2>', 'name'=>'<sub-cat-name2>']	
	public function actionGetDropdown($division = null)
	{
		//TODO RBAC permission check
		try{
			//TODO check headers
			
			//stub data
			$dropdown = [];
			$data = [];
			if($division == null)
			{
				$data["id"] = "Zoltun Kulle";
				$data["name"] = "Zoltun Kulle";
				$dropdown[] = $data;
				$data = [];
				$data["id"] = "Cydaea";
				$data["name"] = "Cydaea";
				$dropdown[] = $data;
				$data = [];
				$data["id"] = "Izual";
				$data["name"] = "Izual";
				$dropdown[] = $data;
				$data = [];
				$data["id"] = "Urzael";
				$data["name"] = "Urzael";
				$dropdown[] = $data;
				$data = [];
			}
			elseif ($division == "Belial")
			{
				$data["id"] = "Zoltun Kulle";
				$data["name"] = "Zoltun Kulle";
				$dropdown[] = $data;
				$data = [];
			}
			elseif ($division == "Azmodan")
			{
				$data["id"] = "Cydaea";
				$data["name"] = "Cydaea";
				$dropdown[] = $data;
				$data = [];
			}
			elseif ($division == "Diablo")
			{
				$data["id"] = "Izual";
				$data["name"] = "Izual";
				$dropdown[] = $data;
				$data = [];
			}
			elseif ($division == "Malthael")
			{
				$data["id"] = "Urzael";
				$data["name"] = "Urzael";
				$dropdown[] = $data;
				$data = [];
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