<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\MasterLeakLog;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Connection;
use yii\helpers\VarDumper;


class MasterLeakLogController extends Controller 
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
		//get UID of user making request
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		$UserUID = BaseActiveController::getUserFromToken()->UserUID;
		
		$headers = getallheaders();
		MasterLeakLog::setClient($headers['X-Client']);
		
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);
		
		$count = count($data['MasterLeakLog']);
		$responseData = [];
		
		for($i = 0; $i < $count; $i++)
		{
			$masterLeakLog = new MasterLeakLog();
			$masterLeakLog->attributes = $data['MasterLeakLog'][$i];
			$masterLeakLog->CreatedUserUID = $UserUID;
			$masterLeakLog->ModifiedUserUID = $UserUID;
			
			if($masterLeakLog->save())
			{
				$responseData[] = ['MasterLeakLogUID'=>$data['MasterLeakLog'][$i]['MasterLeakLogUID'], 'Success'=>1];
			}
			else
			{
				$responseData[] = ['MasterLeakLogUID'=>$data['MasterLeakLog'][$i]['MasterLeakLogUID'], 'Success'=>0];
			}		
		}
		//send response
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->data = $responseData;
		return $response;
	}
}