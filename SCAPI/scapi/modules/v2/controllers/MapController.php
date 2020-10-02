<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\WebManagementMapView;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class MapController extends Controller 
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'get' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGet($mapgrid)
	{
		try
		{
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			
			//RBAC permissions check
			PermissionsController::requirePermission('mapGet', $client);
			
			//create response format
			$responseData = [];
			
			$assets = WebManagementMapView::find()
				->where(['MapGrid' => $mapgrid])
				->orderBy('MapGrid')
				->all();
			$responseData['assets'] = $assets;
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
}