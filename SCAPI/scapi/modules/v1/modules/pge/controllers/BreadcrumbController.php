<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\models\Breadcrumb;
use app\modules\v1\modules\pge\models\PGEBreadcrumb;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class BreadcrumbController extends Controller 
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
					'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionCreate()
	{
		try
		{
			//get http headers
			$headers = getallheaders();
			
			//get post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$userUID = BaseActiveController::getUserFromToken()->UserUID;
			$breadcrumbs = $data["Breadcrumbs"];
			$breadcrumbCount = count($breadcrumbs);
			$responseArray = [];
			
			//traverse breadcrumb array
			for($i = 0; $i < $breadcrumbCount; $i++)
			{
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
				$scBreadcrumb = new Breadcrumb;
				$scBreadcrumb->attributes = $breadcrumbs[$i];
				$scBreadcrumb->BreadcrumbCreatedUserUID = $userUID;
				$scBreadcrumb->BreadcrumbCreatedDate = BaseActiveController::getDate();
				
				BaseActiveRecord::setClient($headers['X-Client']);
				$pgeBreadcrumb = new PGEBreadcrumb;
				$pgeBreadcrumb->attributes = $breadcrumbs[$i];
				$pgeBreadcrumb->BreadcrumbCreatedUserUID = $userUID;
				$pgeBreadcrumb->BreadcrumbCreatedDate = BaseActiveController::getDate();
				
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
				if($scBreadcrumb->save())
				{
					BaseActiveRecord::setClient($headers['X-Client']);
					if($pgeBreadcrumb->save())
					{
						$response->setStatusCode(201);
						$responseArray[] = ['BreadcrumbUID'=>$pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag'=>1];
					}
					else
					{
						$responseArray[] = ['BreadcrumbUID'=>$pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag'=>0];
					}
				}
				else
				{
					$responseArray[] = ['BreadcrumbUID'=>$pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag'=>0];
				}
			}
			//return data in response
			$response->data = $responseArray;
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