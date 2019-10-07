<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\Expense;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class ExpenseController extends Controller{

	public function behaviors(){
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = [
                'class' => VerbFilter::className(),
                'actions' => [
					//'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public static function processExpense($data, $client){
		try{
			//set client header
			BaseActiveRecord::setClient($client);
	
			//try catch to log expense object error
			try{					
				$successFlag = 0;
				$expense = new Expense;
				$expense->attributes = $data;

				if ($expense->save()) {
					$successFlag = 1;
				} else {
					throw BaseActiveController::modelValidationException($expense);
				}
			}catch(\Exception $e){
				//if db exception is 2601, duplicate contraint then success
				//if(in_array($e->errorInfo[1], array(2601, 2627))){
				//	$successFlag = 1;
				//}else{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data);
				$successFlag = 0;
			}
			$responseData = [
				'CreatedDate' => $data['CreatedDate'],
				'ChargeAccount' => $data['ChargeAccount'],
				'SuccessFlag' => $successFlag
			];
			//return response data
			return $responseData;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
}