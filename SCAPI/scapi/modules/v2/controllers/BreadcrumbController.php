<?php

namespace app\modules\v2\controllers;
set_time_limit(600);

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Breadcrumb;
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
			$userName = BaseActiveController::getUserFromToken()->UserName;
			
			//save json to archive
			BaseActiveController::archiveBreadcrumbJson($post, $userName, $headers['X-Client']);
			
			$breadcrumbs = $data["Breadcrumbs"];
			$breadcrumbCount = count($breadcrumbs);
			$responseArray = [];
			
			//traverse breadcrumb array
			for($i = 0; $i < $breadcrumbCount; $i++)
			{
				//try catch to log individual breadcrumb errors
				try
				{	
					$successFlag = 0;
					
					BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
					$breadcrumb = new Breadcrumb;
					$breadcrumb->attributes = $breadcrumbs[$i];
					$breadcrumb->BreadcrumbCreatedUserUID = $userName;
					$breadcrumb->BreadcrumbCreatedDate = BaseActiveController::getDate();

					BaseActiveRecord::setClient($headers['X-Client']);
					$clientBreadcrumb = new Breadcrumb;
					$clientBreadcrumb->attributes = $breadcrumbs[$i];
					$clientBreadcrumb->BreadcrumbCreatedUserUID = $userName;
					$clientBreadcrumb->BreadcrumbCreatedDate = BaseActiveController::getDate();
					
					//check if breadcrumb already exist.
					$previousBreadcrumb = Breadcrumb::find()
						->where(['BreadcrumbUID' => $clientBreadcrumb->BreadcrumbUID])
						->one();

					if ($previousBreadcrumb == null) {
						//point at ct db
						BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
						if ($breadcrumb->save()) {
							//point at client db
							BaseActiveRecord::setClient($headers['X-Client']);
							if ($clientBreadcrumb->save()) {
								$response->setStatusCode(201);
								$successFlag = 1;
							} else {
								throw BaseActiveController::modelValidationException($clientBreadcrumb);
							}
						} else {
							throw BaseActiveController::modelValidationException($breadcrumb);
						}
					}
					else
					{
						//send success if breadcrumb record was already saved previously
						$successFlag = 1;
					}
					$responseArray[] = ['BreadcrumbUID' => $clientBreadcrumb->BreadcrumbUID, 'SuccessFlag' => $successFlag];
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