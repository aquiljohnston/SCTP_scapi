<?php

namespace app\modules\v3\controllers;

use app\modules\v3\constants\Constants;
use app\modules\v3\models\Alert;
use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Query;

class AlertController extends Controller 
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
                    'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGet($projectID)
	{
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('alertGet');

			$data['alerts'] = Alert::find()
				->where(['ProjectID' => $projectID])
				->orderBy(['Severity' => SORT_ASC, 'Title' => SORT_ASC])
				->asArray()
				->all();

			//build and return response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
			return $response;
		} catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            BaseActiveController::archiveWebErrorJson('Alert Get', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
		}
	}
	
	  /**
     * Create New Alert in CT DB
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreate()
    {
        try {
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('alertCreate');
			
            //get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
			
			//archive json
			BaseActiveController::archiveJson($body, 'AlertCreate', BaseActiveController::getUserFromToken()->UserName, BaseActiveController::urlPrefix());
			
			$alertArray = $data['alerts'];
			$alertResponse['alerts'] = [];
			
			foreach($alertArray as $alertData)
			{
				try{
					$alert = new Alert(); 
					$alert->attributes = $alertData;  
					
					if($alert->save())
					{
						$alertResponse['alerts'][] = ['ID' => $alert->ID, 'Title' => $alert->Title, 'SuccessFlag' => 1];
					} else {
						throw BaseActiveController::modelValidationException($alert);
						$alertResponse['alerts'][] = ['Title' => $alert->Title, 'SuccessFlag' => 0];
					}
				} catch (\Exception $e) {
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix(), $alertData);
					$alertResponse['alerts'][] = ['Title' => $alert->Title, 'SuccessFlag' => 0];
				}
			} 
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $alertResponse;
			return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
			throw new \yii\web\HttpException(400);
        }
    }
}