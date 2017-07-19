<?php

namespace app\modules\v2\modules\pge\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\modules\pge\models\MasterLeakLog;
use app\modules\v2\modules\pge\models\InspectionService;
use app\modules\v2\modules\pge\models\TabletMapGrids;
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
					'delete' => ['put'],
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
			$services = [];
			
			for($i = 0; $i < $logCount; $i++)
			{
				//try catch to log individual MLL errors
				try
				{
					$adHocIR = null;
					//check if AdHocIR if so check if IR exist and create if it is missing
					if(array_key_exists('AssetInspectionUID', $logArray[$i]))
					{
						$adHocIR = self::createIR($headers['X-Client'], $UserUID, $logArray[$i]);
					}
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
						else
						{
							throw BaseActiveController::modelValidationException($masterLeakLog);
						}
					}
					
					//if new record or count is greater than 0 process equipment
					if($newMLL || $existingLogCount > 0)
					{
						for($j = 0; $j < $equipmentCount; $j++)
						{
							//reset new inspection service flag
							$newIS = false;
							//get count of current active recrods with matching master leak log and inspection equipment uids
							$existingISCount = InspectionService::find()
								//->where(['ActiveFlag' => 1])
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
								$inspectionService->StatusType = 'In Progress';
								//if new record saves set new record flag to true
								if ($inspectionService->save())
								{
									$newIS = true;
								}
								else
								{
									$e = BaseActiveController::modelValidationException($inspectionService);
									BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $logArray[$i], $equipmentArray[$j]);
								}
							}
							
							//if new record or count is greater than 0 add to response
							if ($newIS || $existingISCount > 0)
							{
								$services[] = ['MasterLeakLogUID' => $logArray[$i]['MasterLeakLogUID'],
								'InspectionEquipmentUID' => $equipmentArray[$j]['InspectionEquipmentUID'],
								'SuccessFlag' => 1];
							}
							else
							{
								$services[] = ['MasterLeakLogUID' => $logArray[$i]['MasterLeakLogUID'],
									'InspectionEquipmentUID' => $equipmentArray[$j]['InspectionEquipmentUID'],
									'SuccessFlag' => 0];
							}
						}
						$responseData[] = ['MasterLeakLogUID'=>$logArray[$i]['MasterLeakLogUID'], 'SuccessFlag'=>1, 'Services' => $services, 'InspectionRequest' => $adHocIR];
					}
					else
					{
						$responseData[] = ['MasterLeakLogUID'=>$logArray[$i]['MasterLeakLogUID'], 'SuccessFlag'=>0, 'Services' => $services, 'InspectionRequest' => $adHocIR];
					}					
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $logArray[$i]);
					$responseData[] = ['MasterLeakLogUID'=>$logArray[$i]['MasterLeakLogUID'], 'SuccessFlag'=>0, 'Services' => $services];
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
	
	public function actionDelete()
	{
		try
		{
			//get UID of user making request
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$UserUID = BaseActiveController::getUserFromToken()->UserUID;
			
			$headers = getallheaders();
			MasterLeakLog::setClient($headers['X-Client']);
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//save json to archive
			BaseActiveController::archiveJson($put, 'MasterLeakLogDelete', $UserUID, $headers['X-Client']);
			
			$leakLogCount = count($data['LeakLogs']);
			
			$responseData = [];
			
			for($i = 0 ; $i < $leakLogCount; $i++)
			{
				$services = [];
				//handle associated inspection services
				$inspectionServices = InspectionService::find()
					->where(['MasterLeakLogUID' => $data['LeakLogs'][$i]])
					->andWhere(['ActiveFlag' => 1])
					->andWhere(['<>','StatusType', 'Deleted'])
					->all();
					
				$inspectionServiceCount = count($inspectionServices);
				$inspectionServiceProcessedCount = 0;
				
				for ($j = 0; $j < $inspectionServiceCount; $j++)
				{
					//$previousInspectionService = new InspectionService();
					$previousInspectionService = $inspectionServices[$j];
					$newInspectionService = new InspectionService();
					//$previousInspectionService->attributes = $inspectionServices[s$j];
					//$newInspectionService->attributes = $inspectionServices[$j];
					$newInspectionService->attributes = $inspectionServices[$j]->attributes;
					//deactivate previous
					$previousInspectionService->ActiveFlag = 0;
					//increment revision
					$newInspectionService->Revision = $previousInspectionService->Revision + 1;
					//set satus to deleted
					$newInspectionService->StatusType = "Deleted";
					if($previousInspectionService->update())
					{
						if($newInspectionService->save())
						{
							$services[] = ['MasterLeakLogUID' => $data['LeakLogs'][$i],
								'InspectionEquipmentUID' => $inspectionServices[$j]['InspectionEquipmentUID'],
								'SuccessFlag' => 1];
						}
						else
						{
							$services[] = ['MasterLeakLogUID' => $data['LeakLogs'][$i],
								'InspectionEquipmentUID' => $inspectionServices[$j]['InspectionEquipmentUID'],
								'SuccessFlag' => 0];
						}
					}
					else
					{
						$services[] = ['MasterLeakLogUID' => $data['LeakLogs'][$i],
								'InspectionEquipmentUID' => $inspectionServices[$j]['InspectionEquipmentUID'],
								'SuccessFlag' => 0];
					}
				}
				
				//handle mll
				$previousMLL = MasterLeakLog::find()
					->where(['MasterLeakLogUID' => $data['LeakLogs'][$i]])
					->andWhere(['ActiveFlag' => 1])
					->andWhere(['<>','StatusType', 'Deleted'])
					->one();
					
				if ($previousMLL != null)
				{
					$newMLL = new MasterLeakLog();
					$newMLL->attributes = $previousMLL->attributes;
					//deactivate previous record
					$previousMLL->ActiveFlag = 0;
					//increment revision
					$newMLL->Revision = $previousMLL->Revision + 1;
					//set satus to deleted
					$newMLL->StatusType = 'Deleted';
					if($previousMLL->update())
					{
						if($newMLL->save())
						{
							$responseData[] = ['MasterLeakLogUID'=> $data['LeakLogs'][$i], 'SuccessFlag'=>1, 'Services' => $services];
						}
						else
						{
							$responseData[] = ['MasterLeakLogUID'=> $data['LeakLogs'][$i], 'SuccessFlag'=>0, 'Services' => $services];
						}
					}
					else
					{
						$responseData[] = ['MasterLeakLogUID'=> $data['LeakLogs'][$i], 'SuccessFlag'=>0, 'Services' => $services];
					}
				}
				else
				{
					$responseData[] = ['MasterLeakLogUID'=> $data['LeakLogs'][$i], 'SuccessFlag'=>1, 'Services' => $services];
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
	
	//function to create ad hoc inspection request when record is missing or broken
	private static function createIR($client, $userUID, $data)
	{
		//get map data based on mapGridUID
		$mapGrid  = TabletMapGrids::find()
			->where(['MapGridsUID'=> $data['MapGridUID']])
			->one();
		
		//create array
		$adHocArray = [];
		//populate array
		$adHocArray['MapPlat'] = "$mapGrid->FuncLocMap\/$mapGrid->FuncLocPlat";
		$adHocArray['PlatPrefix'] = $mapGrid->FuncLocPlatChar2;
		$adHocArray['Plat'] = $mapGrid->FuncLocPlat;
		//remove possible "." from assignedWorkQueueUID
		$adHocArray['AssignedWorkQueueUID'] = WorkQueueController::replacePeriod($data['AssignedWorkQueueUID']);
		$adHocArray['AssignedUserUID'] = $userUID;
		$adHocArray['AssignedInspectionRequestUID'] = $data['InspectionRequestLogUID'];
		$adHocArray['MapGridUID'] = $data['MapGridUID'];
		$adHocArray['SurveyType'] = $data['SurveyType'];
		$adHocArray['WorkCenter'] = $mapGrid->WorkCenter;
		$adHocArray['DispatchMethod'] = 'Ad Hoc';
		$adHocArray['MasterLeakLogUID'] = $data['MasterLeakLogUID'];
		$adHocArray['AssignedDate'] = date("Y-m-d");
		$adHocArray['SourceID'] = $data['SourceID'];
		$adHocArray['SrcDTLT'] = $data['SrcDTLT'];
		$adHocArray['AssetInspectionUID'] = $data['AssetInspectionUID'];

		//pass to adhoc function
		return WorkQueueController::lockAdHoc($adHocArray, $client, $userUID);
	}
}