<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Calibration;
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
					//'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public static function processCalibration($data, $client, $activityID)
	{
		try
		{
			//set client header
			BaseActiveRecord::setClient($client);
			
			$calibrationCount = count($data);
			$responseArray = [];
			
			//traverse calibration array
			for($i = 0; $i < $calibrationCount; $i++)
			{
				//try catch to log individual errors
				try
				{					
					$successFlag = 0;
					$calibrationID = null;
					$newCalibration = new Calibration;
					$newCalibration->attributes = $data[$i];
					$newCalibration->ActivityID = $activityID;
					$date = Date('Y-m-d', strtotime($newCalibration->SrcDTLT));
					
					//check if Calibration already exist.
					$previousCalibration = Calibration::find()
						->where(['CreatedUserID' => $newCalibration->CreatedUserID])
						->andWhere(['SerialNumber' => $newCalibration->SerialNumber])
						->andWhere(['DeletedFlag' => 0])
						->andWhere(['cast([SrcDTLT] as date)' => $date])
						->one();

					if ($previousCalibration == null) {
						if ($newCalibration->save()) {
							$calibrationID = $newCalibration->ID;
							$successFlag = 1;
						} else {
							throw BaseActiveController::modelValidationException($newCalibration);
						}
					}
					else
					{
						//send success if Calibration record was already saved previously
						$calibrationID = $previousCalibration->ID;
						$successFlag = 1;
					}
					$responseArray[] = ['ID' => $calibrationID, 'SerialNumber' => $data[$i]['SerialNumber'], 'SuccessFlag' => $successFlag];
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i]);
					$responseArray[] = ['SerialNumber' => $data[$i]['SerialNumber'], 'SuccessFlag' => $successFlag];
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
}