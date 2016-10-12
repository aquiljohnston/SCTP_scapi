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
use app\modules\v1\modules\pge\models\InspectionService;
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
	
	//takes in an array of new master leak logs and an array of equipment, creates new records for each log 
	//and new place holder inspection service records for each log equipment pair.
	public function actionCreate()
	{
		//get UID of user making request
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		$UserUID = BaseActiveController::getUserFromToken()->UserUID;
		
		$headers = getallheaders();
		MasterLeakLog::setClient($headers['X-Client']);
		
		$put = file_get_contents("php://input");
		$data = json_decode($put, true);
		
		$logArray = $data['MasterLeakLog']['Logs'];
		$equipmentArray = $data['MasterLeakLog']['Equipment'];
		
		$logCount = count($logArray);
		$equipmentCount = count($equipmentArray);
		$responseData = [];
		
		for($i = 0; $i < $logCount; $i++)
		{
			$masterLeakLog = new MasterLeakLog();
			$masterLeakLog->attributes = $logArray[$i];
			$masterLeakLog->CreatedUserUID = $UserUID;
			$masterLeakLog->ModifiedUserUID = $UserUID;
			
			if($masterLeakLog->save())
			{
				$services = [];
				for($j = 0; $j < $equipmentCount; $j++)
				{
					$inspectionService = new InspectionService();
					$inspectionService->attributes = $logArray[$i];
					$inspectionService->InspectionServicesUID = BaseActiveController::generateUID('InspectionService', 'API');
					$inspectionService->InspectionRequestUID = $logArray[$i]['InspectionRequestLogUID'];
					$inspectionService->InspectionEquipmentUID = $equipmentArray[$j]['InspectionEquipmentUID'];
					$inspectionService->CreatedUserUID = $UserUID;
					$inspectionService->ModifiedUserUID = $UserUID;
					$inspectionService->PlaceHolderFlag = 1;
					
					if ($inspectionService->save())
					{
						$services[] = ['InspectionServiceUID' => $inspectionService->InspectionServicesUID,
						'InspectionEquipmentUID' => $inspectionService->InspectionEquipmentUID,
						'Success' => 1];
					}
					else
					{
						$services[] = ['InspectionServiceUID' => $inspectionService->InspectionServicesUID,
							'InspectionEquipmentUID' => $inspectionService->InspectionEquipmentUID,
							'Success' => 0];
					}
				}
				
				$responseData[] = ['MasterLeakLogUID'=>$logArray[$i]['MasterLeakLogUID'], 'Success'=>1, 'Services' => $services];
			}
			else
			{
				$responseData[] = ['MasterLeakLogUID'=>$logArray[$i]['MasterLeakLogUID'], 'Success'=>0, 'Services' => $services];
			}		
		}
		//send response
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->data = $responseData;
		return $response;
	}
}