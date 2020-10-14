<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\db\Query;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\constants\Constants;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\ABCTaskOut;
use app\modules\v3\models\BaseActiveRecord;

class AbcCodesController extends Controller{

	public function behaviors(){
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = [
                'class' => VerbFilter::className(),
                'actions' => [
					'create-task-out' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionCreateTaskOut(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			$createdBy = BaseActiveController::getUserFromToken()->UserName;

			// RBAC permission check
			PermissionsController::requirePermission('abcCodesCreateTaskOut');

			//capture post body
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			//archive json
			BaseActiveController::archiveJson(json_encode($data), 'ABCTaskOut', $createdBy, BaseActiveController::urlPrefix());
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			if(array_key_exists('ABCCodes', $data)){
				//pass data to insert function
				$responseData['ABCCodes'] = self::save($data['ABCCodes']);
			}else{
				$responseData= (object)[];
			}
			
			//return response data
			$response->data = $responseData;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(UnauthorizedHttpException $e) {
            throw new UnauthorizedHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
	}
	
	private static function save($data){
		//start transaction
		$db = BaseActiveRecord::getDb();
		$transaction = $db->beginTransaction();	

		//create response array
		$responseData = [];
		
		//count number of items to insert
		$taskoutCount = count($data);
		for($i = 0; $i < $taskoutCount; $i++){
			//try catch to log expense object error
			try{					
				$successFlag = 0;
				$abcTaskOut = new ABCTaskOut;
				$abcTaskOut->attributes = $data[$i];

				if ($abcTaskOut->save()){
					$successFlag = 1;
				} else {
					throw BaseActiveController::modelValidationException($abcTaskOut);
				}
			}catch(yii\db\Exception $e){
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i], $data);
				$successFlag = 0;
			}
			$responseData[] = [
				'ABCTaskOutUID' => $data[$i]['ABCTaskOutUID'],
				'RefProjectID' => $data[$i]['RefProjectID'],
				'ReportingTaskID' => $data[$i]['ReportingTaskID'],
				'SuccessFlag' => $successFlag
			];
		}
		
		//commit transaction
		$transaction->commit();
		return $responseData;
	}
}