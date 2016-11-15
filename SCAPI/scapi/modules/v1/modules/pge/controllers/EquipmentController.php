<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\TabletDropDownEquipment;
use app\modules\v1\modules\pge\models\InspectionsEquipment;
use app\modules\v1\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class EquipmentController extends Controller 
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
					'update' => ['put'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGet()
	{
		try
		{
			//get UID of user making request
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$UserUID = BaseActiveController::getUserFromToken()->UserUID;
			
			$headers = getallheaders();
			TabletDropDownEquipment::setClient($headers['X-Client']);
			
			$data = TabletDropDownEquipment::find()
				->where(['UserUID' => $UserUID])
				->orderBy('WCAbbrev')
				->all();
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
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
	
	//Parses Equipment Calibration Array from the activity json and calls appropriate helper method to store data into the table.
	public static function calibrationParse($equipmentCalibrationArray, $client, $userUID)
	{
		try
		{
			$responseData = [];

			if($equipmentCalibrationArray != null)
			{
				//loop calibration entries
				$equipmentCalibrationCount = (count($equipmentCalibrationArray));
				for ($i = 0; $i < $equipmentCalibrationCount; $i++)
				{
					//determine appropriate helper method
					if ($equipmentCalibrationArray[$i]["UpdateFlag"] == 0)
					{
						$savedData = self::calibrationCreate($equipmentCalibrationArray[$i], $client, $userUID);
					}
					if ($equipmentCalibrationArray[$i]["UpdateFlag"] == 1)
					{
						$savedData = self::calibrationUpdate($equipmentCalibrationArray[$i], $client, $userUID);
					}
					//add to response array
					$responseData[] = $savedData;
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
	
	//creates a record in the inspections equipment table
	public static function calibrationCreate($calibrationData, $client, $userUID)
	{
		try
		{			
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($client);
			
			if($calibrationData != null)
			{
				$existingCalibration = InspectionsEquipment::find()
					->where(['InspecitonEquipmentUID' => $calibrationData['InspecitonEquipmentUID']])
					->one();
					
				if ($existingCalibration == null)
				{
					//new InspectionsEquipment model
					$calibrationModel = new InspectionsEquipment();
					//pass data to model
					$calibrationModel->attributes = $calibrationData;
					//additional fields
					$calibrationModel->CreatedUserUID = $userUID;
					$calibrationModel->ModifiedUserUID = $userUID;
					
					//save model
					if($calibrationModel->save())
					{
						//add to response array
						$savedData = $calibrationModel;
					}
					else
					{
						$savedData = 'Failed to Create Equipment Calibration Record';
					}
				}
				else
				{
					$savedData = $existingCalibration;
				}
			}
			return $savedData;		
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
	
	//updates a record in the inspections equipment table
	public static function calibrationUpdate($calibrationData, $client, $userUID)
	{
		try
		{			
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($client);
			
			if($calibrationData != null)
			{
				//get previous calibration record
				$previousCalibration = InspectionsEquipment::find()
					->where(['EquipmentLogUID'=> $calibrationData["EquipmentLogUID"]])
					->andWhere(['ActiveFlag' => 1])
					->one();
				$previousCalibration->ActiveFlag = 0;
				$previousCalibration->ModifiedUserUID = $userUID;
				$revisionCount = $previousCalibration->Revision + 1;
				//update previous record
				if($previousCalibration->update())
				{
					//new calibrationModel model
					$calibrationModel = new InspectionsEquipment();
					//pass data to model
					$calibrationModel->attributes = $calibrationData;
					//additional fields
					$calibrationModel->Revision = $revisionCount;
					$calibrationModel->CreatedUserUID = $userUID;
					$calibrationModel->ModifiedUserUID = $userUID;
					
					//save model
					if($calibrationModel->save())
					{
						//add to response array
						$savedData = $calibrationModel;
					}
					else
					{
						$previousCalibration->ActiveFlag = 1;
						$previousCalibration->update();
						$savedData = 'Failed To New Save Equipment Calibration Record';
					}
				}
				else
				{
					$savedData = 'Failed To Update Previous Equipment Calibration Record';
				}
			}
			return $savedData;		
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