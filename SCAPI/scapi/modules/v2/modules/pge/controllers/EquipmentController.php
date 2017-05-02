<?php

namespace app\modules\v2\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\modules\pge\models\TabletDropDownEquipment;
use app\modules\v2\modules\pge\models\InspectionsEquipment;
use app\modules\v2\models\BaseActiveRecord;
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
	
	//Parses Equipment Calibration Array from the activity json and stores it in the database
	public static function calibrationParse($equipmentCalibrationArray, $client, $userUID, $activityUID)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($client);
			
			$responseData = [];
			$savedData = [];

			if($equipmentCalibrationArray != null)
			{
				//loop calibration entries
				$equipmentCalibrationCount = (count($equipmentCalibrationArray));
				for ($i = 0; $i < $equipmentCalibrationCount; $i++)
				{
					//try catch to log individual equipment errors
					try
					{	
						if($equipmentCalibrationArray[$i] != null)
						{
							//check for a previous record with the same UID
							$previousCalibration = InspectionsEquipment::find()
								->where(['InspecitonEquipmentUID' => $equipmentCalibrationArray[$i]['InspecitonEquipmentUID']])
								->andwhere(['ActiveFlag' => 1])
								->one();
							
							$initialPreviousRecord = $previousCalibration;
							
							//if no previous record exist create
							if ($previousCalibration == null)
							{
								//new InspectionsEquipment model
								$calibrationModel = new InspectionsEquipment();
								//pass data to model
								$calibrationModel->attributes = $equipmentCalibrationArray[$i];
								//additional fields
								$calibrationModel->ActivityUID = $activityUID;
								$calibrationModel->CreatedUserUID = $userUID;
								$calibrationModel->ModifiedUserUID = $userUID;
								
								try{
									//save model
									if($calibrationModel->save())
									{
										//add to response array
										$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>1];
									}
									else
									{
										throw BaseActiveController::modelValidationException($calibrationModel);
									}
								}
								catch(yii\db\Exception $e)
								{
									if(in_array($e->errorInfo[1], array(2601, 2627)))
									{
										//catch duplicate records exception
										$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>1];
									}
									else
									{
										BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $equipmentCalibrationArray[$i]);
										$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>0];
									}
								}
							}
							//else update the previous record
							else
							{
								$previousCalibration->ActiveFlag = 0;
								$previousCalibration->ModifiedUserUID = $userUID;
								$revisionCount = $previousCalibration->Revision + 1;
								//update previous record
								if($previousCalibration->update())
								{
									//new calibrationModel model
									$calibrationModel = new InspectionsEquipment();
									//pass data to model
									$calibrationModel->attributes = $equipmentCalibrationArray[$i];
									//additional fields
									$calibrationModel->Revision = $revisionCount;
									$calibrationModel->ActivityUID = $activityUID;
									$calibrationModel->CreatedUserUID = $userUID;
									$calibrationModel->ModifiedUserUID = $userUID;
									
									try{
										//save model
										if($calibrationModel->save())
										{
											//add to response array
											$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>1];
										}
										else
										{
											$previousCalibration->ActiveFlag = 1;
											$previousCalibration->update();
											throw BaseActiveController::modelValidationException($calibrationModel);
										}
									}
									catch(yii\db\Exception $e)
									{
										if(in_array($e->errorInfo[1], array(2601, 2627)))
										{
											//catch duplicate records exception
											$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>1];
										}
										else
										{
											$previousCalibration->ActiveFlag = 1;
											$previousCalibration->update();
											BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $equipmentCalibrationArray[$i]);
											$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>0];
										}
									}
								}
								else
								{
									$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>0];
								}
							}
						}	
					}
					catch(\Exception $e)
					{
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $equipmentCalibrationArray[$i]);
						$savedData = ['InspecitonEquipmentUID'=>$equipmentCalibrationArray[$i]['InspecitonEquipmentUID'], 'SuccessFlag'=>0];
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
}