<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\AssignedWorkQueue;
use app\modules\v1\modules\pge\models\InspectionRequest;
use app\modules\v1\modules\pge\models\TabletMapGrids;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Connection;
use yii\helpers\VarDumper;


class WorkQueueController extends Controller 
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
					'get' => ['get'],
					'lock' => ['get'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGet()
	{
		try
		{
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$UID = BaseActiveController::getUserFromToken()->UserUID;
			
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$connection = BaseActiveRecord::getDb();
			
			$workQueueCommand = $connection->createCommand("SELECT * From fnTabletIR(:UserUID) Order by SortOrder, WorkCenter")
				->bindParam(':UserUID', $UID,  \PDO::PARAM_STR);
			$resultSet = $workQueueCommand->queryAll();

			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $resultSet;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public static function lockRecords($workQueueArray, $client, $userUID)
	{
		try
		{
			$responseData = [];

			if($workQueueArray != null)
			{
				//loop calibration entries
				$workQueueCount = (count($workQueueArray));
				for ($i = 0; $i < $workQueueCount; $i++)
				{
					if($workQueueArray[$i]['DispatchMethod'] == 'Dispatched')
					{
						$responseData[] = self::lockDispatched($workQueueArray[$i], $client, $userUID);
					}
					elseif($workQueueArray[$i]['DispatchMethod'] == 'Self Dispatch')
					{
						$responseData[] = self::lockSelfDispatched($workQueueArray[$i], $client, $userUID);
					}
					elseif($workQueueArray[$i]['DispatchMethod'] == 'Ad Hoc')
					{
						$responseData[] = self::lockAdHoc($workQueueArray[$i], $client, $userUID);
					}
					else
					{
						$responseData[] = ['AssignedInspectionRequestUID'=>$workQueueArray[$i]['AssignedInspectionRequestUID'], 'AssignedWorkQueueUID'=>$workQueueArray[$i]['AssignedWorkQueueUID'], 'LockedFlag'=>0];
					}
				}
			}
			return $responseData;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	//helper method for actionLock to handle a record of DispatchMethod: Dispatched
	public static function lockDispatched($workQueue, $client, $userUID)
	{
		try
		{
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			//get previous record
			$previousRecord = AssignedWorkQueue::find()
				->where(['AssignedWorkQueueUID' => $workQueue['AssignedWorkQueueUID']])
				->andWhere(['ActiveFlag' => 1])
				->one();
			$previousRecord->ModifiedUserUID = $userUID;
			//deactivate previous record
			$previousRecord->ActiveFlag = 0;
			//get previous revision and increment by 1
			$revisionCount = $previousRecord->Revision + 1;
			
			if($previousRecord->update())
			{
				//new AssignedWorkQueue model
				$newRecord = new AssignedWorkQueue;
				$newRecord->attributes = $workQueue;
				//additionalFields
				$newRecord->CreatedUserUID = $userUID;
				$newRecord->ModifiedUserUID = $userUID;
				$newRecord->Revision = $revisionCount;
				$newRecord->RevisionComments = 'In Progress: Record Locked';
				$newRecord->LockedFlag = 1;
				$newRecord->AssignedUserUID = $userUID;
				
				if($newRecord->save())
				{
					return $newRecord;
				}
				else
				{
					$previousRecord->ActiveFlag = 1;
					$previousRecord->update();
					return ['AssignedInspectionRequestUID'=>$workQueue['AssignedInspectionRequestUID'], 'AssignedWorkQueueUID'=>$workQueue['AssignedWorkQueueUID'], 'LockedFlag'=>0];
				}
			}
			else
			{
				return ['AssignedInspectionRequestUID'=>$workQueue['AssignedInspectionRequestUID'], 'AssignedWorkQueueUID'=>$workQueue['AssignedWorkQueueUID'], 'LockedFlag'=>0];
			}
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	//helper method for actionLock to handle a record of DispatchMethod: Self Dispatched
	public static function lockSelfDispatched($workQueue, $client, $userUID)
	{
		try
		{
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			//new AssignedWorkQueue model
			$newRecord = new AssignedWorkQueue;
			$newRecord->attributes = $workQueue;
			//additionalFields
			$newRecord->CreatedUserUID = $userUID;
			$newRecord->ModifiedUserUID = $userUID;
			$newRecord->LockedFlag = 1;
			$newRecord->AssignedUserUID = $userUID;
			
			if($newRecord->save())
			{
				return $newRecord;
			}
			else
			{
				return ['AssignedInspectionRequestUID'=>$workQueue['AssignedInspectionRequestUID'], 'AssignedWorkQueueUID'=>$workQueue['AssignedWorkQueueUID'], 'LockedFlag'=>0];
			}
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	//helper method for actionLock to handle a record of DispatchMethod: Ad Hoc
	public static function lockAdHoc($workQueue, $client, $userUID)
	{
		try
		{
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			//get map data based on map gridUID
			$mapGrid  = TabletMapGrids::find()
				->where(['MapGridsUID'=> $workQueue['MapGridUID']])
				->one();
				
			//create new sudo inspection request
			$sudoIR = new InspectionRequest();
			
			//pass data to sudo IR
			$sudoIR->InspectionRequestUID = $workQueue['AssignedInspectionRequestUID'];
			$sudoIR->SourceID = $workQueue['SourceID'];
			$sudoIR->MapGridUID = $workQueue['MapGridUID'];
			$sudoIR->CreatedUserUID = $userUID;
			$sudoIR->ModifiedUserUID = $userUID;
			$sudoIR->CreateDTLT = BaseActiveController::getDate();
			$sudoIR->ModifiedDTLT = BaseActiveController::getDate();
			$sudoIR->Comments = "Sudo Inspection Request For An Ad Hoc Record";
			//$sudoIR->SurveyType = $workQueue->SurveyType; //not sure if this is sent from tablet
			$sudoIR->MapID = $mapGrid['FuncLocMap'] . "-" . $mapGrid['FuncLocPlat'];
			$sudoIR->Wall = $mapGrid['FuncLocMap'];
			$sudoIR->Plat = $mapGrid['FuncLocPlat'];
			$sudoIR->MWC = $mapGrid['FuncLocMWC'];
			$sudoIR->FLOC = $mapGrid['FLOC'];
			
			//save sudo IR
			if($sudoIR->save())
			{
				//update for missing fields
				//new AssignedWorkQueue model
				$newRecord = new AssignedWorkQueue;
				$newRecord->attributes = $workQueue;
				//additionalFields
				$newRecord->CreatedUserUID = $userUID;
				$newRecord->ModifiedUserUID = $userUID;
				$newRecord->LockedFlag = 1;
				$newRecord->AssignedUserUID = $userUID;
				
				if($newRecord->save())
				{
					return $newRecord;
				}
				else
				{
					return ['AssignedInspectionRequestUID'=>$workQueue['AssignedInspectionRequestUID'], 'AssignedWorkQueueUID'=>$workQueue['AssignedWorkQueueUID'], 'LockedFlag'=>0];
				}
			}
			else
			{
				return ['AssignedInspectionRequestUID'=>$workQueue['AssignedInspectionRequestUID'], 'AssignedWorkQueueUID'=>$workQueue['AssignedWorkQueueUID'], 'LockedFlag'=>0];
			}
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
}