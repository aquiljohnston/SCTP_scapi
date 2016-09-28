<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\TabletEquipment;
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
					'get' => ['get']
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
			TabletEquipment::setClient($headers['X-Client']);
			
			$data = TabletEquipment::find()
				->orderBy('MWC')
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
	
		//Parses Wind Speed Array from the activity json and stores data into the table.
	public static function calibrationCreate($equipmentCalibrationArray, $client, $userUID)
	{
		try
		{
			//set db target
			BaseActiveRecord::setClient($client);
			
			$savedData = [];

			//Indications
			if($equipmentCalibrationArray != null)
			{
				//loop wind speed entries
				$equipmentCalibrationCount = (count($equipmentCalibrationArray));
				for ($i = 0; $i < $equipmentCalibrationCount; $i++)
				{
					//new WindSpeed model
					$equipmentCalibration = new InspectionsEquipment();
					//pass data to model
					$equipmentCalibration->attributes = $equipmentCalibrationArray[$i];
					//additional fields
					$equipmentCalibration->CreatedUserUID = $userUID;
					$equipmentCalibration->ModifiedUserUID = $userUID;
					
					//save model
					if($equipmentCalibration->save())
					{
						//add to response array
						$savedData[] = $equipmentCalibration;
					}
					else
					{
						$savedData[] = 'Failed to Save Equipment Calibration Record';
					}
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