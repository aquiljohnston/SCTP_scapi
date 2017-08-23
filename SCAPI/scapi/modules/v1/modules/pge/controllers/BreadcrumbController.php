<?php

namespace app\modules\v1\modules\pge\controllers;
set_time_limit(600);

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
			
			//save json to archive
			BaseActiveController::archiveBreadcrumbJson($post, $userUID, $headers['X-Client']);
			
			$breadcrumbs = $data["Breadcrumbs"];
			$breadcrumbCount = count($breadcrumbs);
			$responseArray = [];
			
			//traverse breadcrumb array
			for($i = 0; $i < $breadcrumbCount; $i++)
			{
				//try catch to log individual breadcrumb errors
				try
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

					BaseActiveRecord::setClient($headers['X-Client']);
					if ($pgeBreadcrumb->save()) {
						//point at ct db
						BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
						if ($scBreadcrumb->save()) {
							$response->setStatusCode(201);
							$responseArray[] = ['BreadcrumbUID' => $pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag' => 1];
						} else {
							$responseArray[] = ['BreadcrumbUID' => $pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag' => 0];
						}
					} else {
						$responseArray[] = ['BreadcrumbUID' => $pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag' => 0];
					}
				}
				catch(yii\db\Exception $e)
				{
					if(in_array($e->errorInfo[1], array(2601, 2627)))
					{
						$responseArray[] = ['BreadcrumbUID' => $pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag' => 1];
					}
					else
					{
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $breadcrumbs[$i]);
						$responseArray[] = ['BreadcrumbUID' => $breadcrumbs[$i]['BreadcrumbUID'], 'SuccessFlag' => 0];
					}
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $breadcrumbs[$i]);
					$responseArray[] = ['BreadcrumbUID' => $breadcrumbs[$i]['BreadcrumbUID'], 'SuccessFlag' => 0];
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
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
}