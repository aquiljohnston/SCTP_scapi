<?php

namespace app\modules\v1\controllers;

use Yii;
use app\authentication\TokenAuth;
// use app\modules\v1\authentication\TokenAuth;
//use app\modules\v1\models\Division;
use app\modules\v1\controllers\BaseActiveController;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;
use \DateTime;


/**
 * DivisionController creates user notifications.
 */
class WeekController extends Controller
//swap when model is implemented
//class DivisionController extends BaseActiveController
{	
	
	//use when model is implemented
	//public $modelClass = 'app\modules\v1\models\Division'; 
	
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
		//try{
			//TODO check headers
			
			$dropdown = [];
			$w1sun = new DateTime("07/24/16");
			$w1sun = $w1sun->format('m/d/Y');
			$w1sat = new DateTime("07/30/16");
			$w1sat = $w1sat->format('m/d/Y');
			$w2sun = new DateTime("07/17/16");
			$w2sun = $w2sun->format('m/d/Y');
			$w2sat = new DateTime("07/23/16");
			$w2sat = $w2sat->format('m/d/Y');
			$w3sun = new DateTime("07/10/16");
			$w3sun = $w3sun->format('m/d/Y');
			$w3sat = new DateTime("07/16/16");
			$w3sat = $w3sat->format('m/d/Y');
			//stub data
			$dropdown[$w1sun . " - " . $w1sat] = $w1sun . " - " . $w1sat;
			$dropdown[$w2sun . " - " . $w2sat] = $w2sun . " - " . $w2sat;
			$dropdown[$w3sun . " - " . $w3sat] = $w3sun . " - " . $w3sat;
			
			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $dropdown;
			return $response;
		// }
		// catch(ForbiddenHttpException $e)
		// {
			// throw new ForbiddenHttpException;
		// }
		// catch(\Exception $e) 
		// {
			// throw new \yii\web\HttpException(400);
		// }
	}
}