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
use app\modules\v3\models\Question;
use app\modules\v3\models\BaseActiveRecord;

class QuestionController extends Controller{

	public function behaviors(){
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = [
                'class' => VerbFilter::className(),
                'actions' => [
					'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionCreate(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			$createdBy = BaseActiveController::getUserFromToken()->UserName;

			// RBAC permission check
			PermissionsController::requirePermission('questionCreate');

			//capture post body
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			//archive json
			BaseActiveController::archiveJson(json_encode($data), 'QuestionCreate', $createdBy, BaseActiveController::urlPrefix());
			
			if(array_key_exists('Questions', $data)){
				//pull data from envelope
				$data = $data['Questions'];
				//start transaction
				$db = BaseActiveRecord::getDb();
				$transaction = $db->beginTransaction();

				//create response array
				$responseData = [];
				
				//count number of items to insert
				$questionCount = count($data);
				for($i = 0; $i < $questionCount; $i++){
					//try catch to log expense object error
					try{					
						$successFlag = 0;
						$question = new Question;
						$question->attributes = $data[$i];

						if ($question->save()){
							$successFlag = 1;
						} else {
							throw BaseActiveController::modelValidationException($question);
						}
					}catch(yii\db\Exception $e){
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i], $data);
						$successFlag = 0;
					}
					$responseData['Questions'][] = [
						'QuestionUID' => $data[$i]['QuestionUID'],
						'RefProjectID' => $data[$i]['RefProjectID'],
						'RefQuestionID' => $data[$i]['RefQuestionID'],
						'SuccessFlag' => $successFlag
					];
				}
			
				//commit transaction
				$transaction->commit();
			}else{
				$responseData = (object)[];
			}
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;

			//return response data
			$response->data = $responseData;
			return $response;
		}catch(ForbiddenHttpException $e){
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw new ForbiddenHttpException;
        }catch(UnauthorizedHttpException $e) {
            BaseActiveController::logError($e, 'Unauthorized http exception');
            throw new UnauthorizedHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
	}
}