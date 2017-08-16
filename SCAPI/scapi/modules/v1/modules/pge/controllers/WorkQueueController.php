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
use app\modules\v1\modules\pge\models\AssetInspection;
use app\modules\v1\modules\pge\models\Asset;
use app\modules\v1\modules\pge\models\DropDowns;
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
	
	public function actionGetAssigned()
	{
		try
		{
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$UID = BaseActiveController::getUserFromToken()->UserUID;
			
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$connection = BaseActiveRecord::getDb();
			
			$workQueueCommand = $connection->createCommand("SELECT * From fnTabletIR(:UserUID) Where SortOrder=0 Order by SortOrder, WorkCenter")
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
					//remove "." from AssignedWorkQueueUID
					if(array_key_exists('AssignedWorkQueueUID', $workQueueArray[$i]))
					{
						$workQueueArray[$i]['AssignedWorkQueueUID'] = self::replacePeriod($workQueueArray[$i]['AssignedWorkQueueUID']);
					}
					
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
		//NOTE: any existing self dispatch work queue should have come up from the tablet and the lock flag should always be 1.
		//So I'm not checking this flag on my find. If something changes and this flag is not 1 this could cause an issue, and we would need
		//to implement a check on the flag and an update if a record exist with a 0 flag.
		try
		{
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			//check if AssignedWorkQueue exist
			$previousWorkQueue = AssignedWorkQueue::find()
				->where(['AssignedWorkQueueUID' => $workQueue['AssignedWorkQueueUID']])
				->andWhere(['ActiveFlag' => 1])
				->one();
			
			if ($previousWorkQueue != null)
			{
				return $previousWorkQueue;
			}
			else
			{				
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
			
			//check if SudoIR exist
			$previousSudoIR = InspectionRequest::find()
				->where(['InspectionRequestUID' => $workQueue['AssignedInspectionRequestUID']])
				->andWhere(['ActiveFlag' => 1])
				->one();
			
			//flags for checks
			$sudoIRSaved = false;
			
			if($previousSudoIR == null)
			{			
				//get map data based on map gridUID
				$mapGrid  = TabletMapGrids::find()
					->where(['MapGridsUID'=> $workQueue['MapGridUID']])
					->one();
				
				//create new sudo inspection request
				$sudoIR = new InspectionRequest();
				
				//get inspection frequency type
				$frequencyType = DropDowns::find()
					->select('FieldValue')
					->where(['FilterName' => 'ddSurveyFrequencyTR'])
					->andWhere(['FieldDisplay' => $workQueue['SurveyType']])
					->one();
				
				//pass data to sudo IR
				$sudoIR->InspectionRequestUID = $workQueue['AssignedInspectionRequestUID'];
				$sudoIR->SourceID = $workQueue['SourceID'];
				$sudoIR->MapGridUID = $workQueue['MapGridUID'];
				$sudoIR->CreatedUserUID = $userUID;
				$sudoIR->ModifiedUserUID = $userUID;
				$sudoIR->CreateDTLT = BaseActiveController::getDate();
				$sudoIR->ModifiedDTLT = BaseActiveController::getDate();
				$sudoIR->Comments = "Sudo Inspection Request For An Ad Hoc Record"; 
				$sudoIR->MapID = $mapGrid['FuncLocMap'] . "-" . $mapGrid['FuncLocPlat'];
				$sudoIR->Wall = $mapGrid['FuncLocMap'];
				$sudoIR->Plat = $mapGrid['FuncLocPlat'];
				$sudoIR->MWC = $mapGrid['FuncLocMWC'];
				$sudoIR->FLOC = $mapGrid['FLOC'];
				$sudoIR->StatusType = 'In Progress';
				$sudoIR->AdhocFlag = 1;
				$sudoIR->SurveyType = $workQueue['SurveyType'];
				if ($frequencyType != null)
				{
					$sudoIR->InspectionFrequencyType = $frequencyType->FieldValue;
				}
				try
				{
					//save sudo IR
					if($sudoIR->save())
					{
						$sudoIRSaved = true;
					}
				}
				catch(yii\db\Exception $e)
				{
					if(in_array($e->errorInfo[1], array(2601, 2627)))
					{
						$sudoIRSaved = true;
					}
					else
					{
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], null, $workQueue);
					}
				}
			}
			else
			{
				$sudoIRSaved = true;
			}
			
			//check IR
			if($sudoIRSaved)
			{
				//check if AssetInspection exist
				$previousAssetInspection = AssetInspection::find()
					->where(['AssetInspectionUID' => $workQueue['AssetInspectionUID']])
					->andWhere(['ActiveFlag' => 1])
					->one();
				
				$assetInspectionSaved = false;
				
				if($previousAssetInspection == null)
				{
					//get asset UID based on map grid
					$asset = Asset::find()
						->select('AssetUID')
						->where(['MapGridUID' => $workQueue['MapGridUID']])
						->andWhere(['ActiveFlag' => 1])
						->one();
						
					$assetInspection = new AssetInspection;
					$assetInspection->AssetInspectionUID = $workQueue['AssetInspectionUID'];
					$assetInspection->AssetUID = $asset->AssetUID;
					$assetInspection->MapGridUID = $workQueue['MapGridUID'];
					$assetInspection->InspectionRequestUID = $workQueue['AssignedInspectionRequestUID'];
					$assetInspection->SourceID = $workQueue['SourceID'];
					$assetInspection->CreatedUserUID = $userUID;
					$assetInspection->ModifiedUserUID = $userUID;
					
					//try catch for sql constraints 
					try
					{
						if($assetInspection->save())
						{
							$assetInspectionSaved = true;
						}
					}
					catch(yii\db\Exception $e)
					{
						if(in_array($e->errorInfo[1], array(2601, 2627)))
						{
							$assetInspectionSaved = true;
						}
						else
						{
							BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], null, $workQueue);
						}
					}
				}
				else{
					$assetInspectionSaved = true;
				}
				
				if($assetInspectionSaved)
				{
					//check if AssignedWorkQueue exist
					$previousAssignedWorkQueue = AssignedWorkQueue::find()
						->where(['AssignedWorkQueueUID' => $workQueue['AssignedWorkQueueUID']])
						->andWhere(['ActiveFlag' => 1])
						->one();
						
					$previousAssignedWorkQueueSaved = false;
					$newAssignedWorkQueueSaved = false;
						
					if($previousAssignedWorkQueue == null)
					{
						//update for missing fields
						//new AssignedWorkQueue model
						$assignedWorkQueue = new AssignedWorkQueue;
						$assignedWorkQueue->attributes = $workQueue;
						//additionalFields
						$assignedWorkQueue->CreatedUserUID = $userUID;
						$assignedWorkQueue->ModifiedUserUID = $userUID;
						$assignedWorkQueue->LockedFlag = 1;
						$assignedWorkQueue->AssignedUserUID = $userUID;
						try{
							if($assignedWorkQueue->save())
							{
								$newAssignedWorkQueueSaved = true;
							}
						}
						catch(yii\db\Exception $e)
						{
							if(in_array($e->errorInfo[1], array(2601, 2627)))
							{
								$newAssignedWorkQueueSaved = true;
							}
							else
							{
								BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], null, $workQueue);
							}
						}
					}
					else{
						$previousAssignedWorkQueueSaved = true;
					}
					
					if($newAssignedWorkQueueSaved)
					{
						return $assignedWorkQueue;
					}
					elseif($previousAssignedWorkQueueSaved)
					{
						return $previousAssignedWorkQueue;
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
	
	public static function replacePeriod($string)
	{
		return str_replace('.', '', $string);
	}
}