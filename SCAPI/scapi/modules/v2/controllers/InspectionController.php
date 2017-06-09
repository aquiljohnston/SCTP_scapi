<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Inspection;
use app\modules\v2\models\Event;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class InspectionController extends Controller 
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
					//'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public static function processInspection($data, $client, $activityID)
	{
		try
		{
			//set client header
			BaseActiveRecord::setClient($client);
			
			$inspectionCount = count($data);
			$responseArray = [];
			
			//traverse Inspection array
			for($i = 0; $i < $inspectionCount; $i++)
			{
				//try catch to log individual errors
				try
				{	
					$inspectionSuccessFlag = 0;
					$eventResponse = [];
					$inspectionID = null;
				
					$newInspection = new Inspection;
					$newInspection->attributes = $data[$i];
					$newInspection->ActivityID = $activityID;
					
					//check if pge Inspection already exist.
					$previousInspection = Inspection::find()
						->where(['InspectionTabletID' => $newInspection->InspectionTabletID])
						//->andWhere(['DeletedFlag' => 0]) no flag exist currently
						->one();

					if ($previousInspection == null) {
						if ($newInspection->save()) {
							$inspectionSuccessFlag = 1;
							$inspectionID = $newInspection->ID;
							//$responseArray[] = ['ID' => $newInspection->ID, 'InspectionTabletID' => $newInspection->InspectionTabletID, 'SuccessFlag' => 1];
						} else {
							throw BaseActiveController::modelValidationException($newInspection);
						}
					}
					else
					{
						//Handle updates if applicable.
						//send success if Inspection record was already saved previously
						$inspectionSuccessFlag = 1;
						$inspectionID = $previousInspection->ID;
						//$responseArray[] = ['ID' => $previousInspection->ID, 'InspectionTabletID' => $newInspection->InspectionTabletID, 'SuccessFlag' => 1];
					}
					//process event data if available
					if(array_key_exists('Event', $data[$i]))
					{
						$eventResponse = self::processEvent($data[$i]['Event'], $client, $activityID, $inspectionID);
					}
					$responseArray[] = ['ID' => $inspectionID, 'InspectionTabletID' => $newInspection->InspectionTabletID, 'SuccessFlag' => $inspectionSuccessFlag, 'Event' => $eventResponse];
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i]);
					$responseArray[] = ['ID' => $inspectionID,'InspectionTabletID' => $data[$i]['InspectionTabletID'],'SuccessFlag' => $inspectionSuccessFlag, 'Event' => $eventResponse];
				}
			}
			//return response data
			
			return $responseArray;
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
	
	private static function processEvent($data, $client, $activityID, $inspectionID)
	{
		//set client header
		BaseActiveRecord::setClient($client);
		$eventCount = count($data);
		$eventResponse = [];		
		//traverse Event array
		for($i = 0; $i < $eventCount; $i++)
		{
			//try catch to log individual errors
			try
			{	
				$eventSuccessFlag = 0;
				$eventID = null;
			
				$newEvent = new Event;
				$newEvent->attributes = $data[$i];
				$newEvent->InspectionID = $inspectionID;
				
				//check if pge Inspection already exist.
				$previousEvent = Event::find()
					->where(['EventTabletID' => $newEvent->EventTabletID])
					//->andWhere(['DeletedFlag' => 0]) no flag exist currently
					->one();
					
				if ($previousEvent == null) {
					if ($newEvent->save()) {
						$eventSuccessFlag = 1;
						$eventID = $newEvent->ID;
					} else {
						throw BaseActiveController::modelValidationException($newEvent);
					}
				}
				else
				{
					//Handle updates if applicable.
					//send success if Event record was already saved previously
					$eventSuccessFlag = 1;
					$eventID = $previousEvent-ID;
				}
				$eventResponse[] = ['ID' => $eventID, 'EventTabletID' => $data[$i]['EventTabletID'],'SuccessFlag' => $eventSuccessFlag];
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i]);
				$eventResponse[] = ['EventTabletID' => $data[$i]['EventTabletID'],'SuccessFlag' => $eventSuccessFlag];
			}
			return $eventResponse;
		}
	}
	
	private static function processAsset()
	{
		
	}
}