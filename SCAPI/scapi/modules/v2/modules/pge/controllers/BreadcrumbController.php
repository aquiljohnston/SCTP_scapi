<?php

namespace app\modules\v2\modules\pge\controllers;
set_time_limit(600);

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Breadcrumb;
use app\modules\v2\modules\pge\models\PGEBreadcrumb;
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
					
					//check if pge breadcrumb already exist.
					$previousBreadcrumb = PGEBreadcrumb::find()
						->where(['BreadcrumbUID' => $pgeBreadcrumb->BreadcrumbUID])
						->one();

					if ($previousBreadcrumb == null) {
						//point at ct db
						BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
						if ($scBreadcrumb->save()) {
							//point at client db
							BaseActiveRecord::setClient($headers['X-Client']);
							if ($pgeBreadcrumb->save()) {
								$response->setStatusCode(201);
								$responseArray[] = ['BreadcrumbUID' => $pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag' => 1];
							} else {
								//model validation exception
								throw BaseActiveController::modelValidationException($pgeBreadcrumb);
							}
						} else {
							//model validation exception
							throw BaseActiveController::modelValidationException($scBreadcrumb);
						}
					}
					else
					{
						//send success if breadcrumb record was already saved previously
						$responseArray[] = ['BreadcrumbUID' => $pgeBreadcrumb->BreadcrumbUID, 'SuccessFlag' => 1];
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