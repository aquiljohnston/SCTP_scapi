<?php

namespace app\modules\v3\controllers;
set_time_limit(600);
ini_set('memory_limit', '-1');

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\Breadcrumb;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

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
		//TODO handle SCCT as client header and avoid double save
		//Consider implementing constraints similar to pge and reworking format as such
		try{
			//get http headers
			$headers = getallheaders();
			
			//get post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
			PermissionsController::requirePermission('breadcrumbCreate');
			
			$userName = BaseActiveController::getUserFromToken()->UserName;
			
			//save json to archive
			BaseActiveController::archiveBreadcrumbJson($post, $userName, $headers['X-Client']);
			
			$breadcrumbs = $data["Breadcrumbs"];
			$breadcrumbCount = count($breadcrumbs);
			$responseArray = [];
			
			//traverse breadcrumb array
			for($i = 0; $i < $breadcrumbCount; $i++){
				//try catch to log individual breadcrumb errors
				try{	
					$successFlag = 0;
					//fix to prevent scientific notation pace of travel from causing errors
					$breadcrumbs[$i]['PaceOfTravel'] = round($breadcrumbs[$i]['PaceOfTravel'], 5);
					
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
					
					//point at ct db
					BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
					if ($breadcrumb->save()) {
						//point at client db
						BaseActiveRecord::setClient($headers['X-Client']);
						//or should be true after isSCCT check if client is SCCT and ignore the second half and avoid duplicate saves
						if (BaseActiveController::isSCCT($headers['X-Client']) || $clientBreadcrumb->save()) {
							$response->setStatusCode(201);
							$successFlag = 1;
						} else {
							throw BaseActiveController::modelValidationException($breadcrumb);
						}
					} else {
						throw BaseActiveController::modelValidationException($clientBreadcrumb);
					}
					$responseArray[] = ['BreadcrumbUID' => $clientBreadcrumb->BreadcrumbUID, 'SuccessFlag' => $successFlag];
				}catch(yii\db\Exception $e){
					if(in_array($e->errorInfo[1], array(2601, 2627))){
						$responseArray[] = ['BreadcrumbUID' => $clientBreadcrumb->BreadcrumbUID, 'SuccessFlag' => 1];
					}else{
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $breadcrumbs[$i]);
						$responseArray[] = ['BreadcrumbUID' => $breadcrumbs[$i]['BreadcrumbUID'], 'SuccessFlag' => 0];
					}
				}catch(\Exception $e){
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $breadcrumbs[$i]);
					$responseArray[] = ['BreadcrumbUID' => $breadcrumbs[$i]['BreadcrumbUID'], 'SuccessFlag' => 0];
				}
			}
			//return data in response
			$response->data = $responseArray;
			return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
		} catch(UnauthorizedHttpException $e){
			throw new UnauthorizedHttpException();
        } catch(\Exception $e) {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
}