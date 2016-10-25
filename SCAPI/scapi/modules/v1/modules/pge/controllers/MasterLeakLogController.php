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
		try
		{
			//get UID of user making request
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$UserUID = BaseActiveController::getUserFromToken()->UserUID;
			
			$headers = getallheaders();
			MasterLeakLog::setClient($headers['X-Client']);
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			
			//save json to archive
			BaseActiveController::archiveJson($post, 'MasterLeakLogCreate', $UserUID, $headers['X-Client']);
			
			$logArray = $data['MasterLeakLog']['Logs'];
			$equipmentArray = $data['MasterLeakLog']['Equipment'];
			
			$logCount = count($logArray);
			$equipmentCount = count($equipmentArray);
			$responseData = [];
			
			for($i = 0; $i < $logCount; $i++)
			{
				//reset new master leak log flag
				$newMLL = false;
				//get count of current active records with matching master leak log uid
				$existingLogCount = MasterLeakLog::find()
					->where(['ActiveFlag' => 1])
					->andWhere(['MasterLeakLogUID' => $logArray[$i]['MasterLeakLogUID']])
					->count();
				
				//if count is less than 1 create a new record
				if($existingLogCount < 1)
				{
					$masterLeakLog = new MasterLeakLog();
					$masterLeakLog->attributes = $logArray[$i];
					$masterLeakLog->CreatedUserUID = $UserUID;
					$masterLeakLog->ModifiedUserUID = $UserUID;
					//if new record saves set new record flag to true
					if ($masterLeakLog->save()) 
					{
						$newMLL = true;
					}
				}
				
				//if new record or count is greater than 0 process equipment
				if($newMLL || $existingLogCount > 0)
				{
					$services = [];
					for($j = 0; $j < $equipmentCount; $j++)
					{
						//reset new inspection service flag
						$newIS = false;
						//get count of current active recrods with matching master leak log and inspection equipment uids
						$existingISCount = InspectionService::find()
							->where(['ActiveFlag' => 1])
							->andWhere(['MasterLeakLogUID' => $logArray[$i]['MasterLeakLogUID']])
							->andWhere(['InspectionEquipmentUID' => $equipmentArray[$j]['InspectionEquipmentUID']])
							->count();
						
						//if count is less than 1 create a new record
						if($existingISCount < 1)
						{
							$inspectionService = new InspectionService();
							$inspectionService->attributes = $logArray[$i];
							$inspectionService->InspectionServicesUID = BaseActiveController::generateUID('InspectionService', 'API');
							$inspectionService->InspectionRequestUID = $logArray[$i]['InspectionRequestLogUID'];
							$inspectionService->InspectionEquipmentUID = $equipmentArray[$j]['InspectionEquipmentUID'];
							$inspectionService->CreatedUserUID = $UserUID;
							$inspectionService->ModifiedUserUID = $UserUID;
							$inspectionService->PlaceHolderFlag = 1;
							//if new record saves set new record flag to true
							if ($inspectionService->save())
							{
								$newIS = true;
							}
						}
						
						//if new record or count is greater than 0 add to response
						if ($newIS || $existingISCount > 0)
						{
							$services[] = ['MasterLeakLogUID' => $logArray[$i]['MasterLeakLogUID'],
							'InspectionEquipmentUID' => $equipmentArray[$j]['InspectionEquipmentUID'],
							'Success' => 1];
						}
						else
						{
							$services[] = ['MasterLeakLogUID' => $logArray[$i]['MasterLeakLogUID'],
								'InspectionEquipmentUID' => $equipmentArray[$j]['InspectionEquipmentUID'],
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