<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\Calibration;
use app\modules\v3\models\Equipment;
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
					'create' => ['post'],
					'delete-calibration' => ['put'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionCreate()
	{
		try{
			//set db
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('equipmentCreate');
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true)['equipment'];
			//create response format
			$responseData = [];
			
			$createdBy = BaseActiveController::getUserFromToken()->UserName;
			$equipmentID = null;
			$equipmentSerialNumber = $data['EquipmentSerialNumber'];
			$successFlag = 0;
			
			BaseActiveController::archiveJson($body, 'EquipmentCreate', BaseActiveController::getClientUser(BaseActiveController::urlPrefix())->UserName, BaseActiveController::urlPrefix());
			
			$existingEquipment = Equipment::find()
				->where(['EquipmentSerialNumber' => $data['EquipmentSerialNumber']])
				->one();
				
			if($existingEquipment == null){
				//create new equipment if none exist
				$equipment = new Equipment();
				$equipment->attributes = $data;
				$equipment->EquipmentCreatedBy = $createdBy;

				if($equipment->save())
				{
					$equipmentID =  $equipment->EquipmentID;
					$successFlag = 1;
				}
				else
				{
					throw BaseActiveController::modelValidationException($equipment);
				}
			}else{
				//update existing equipment
				$existingEquipment->EquipmentAssignedUserName = $data['EquipmentAssignedUserName'];
				$existingEquipment->EquipmentModifiedDate = $data['EquipmentCreateDate'];
				$existingEquipment->EquipmentModifiedBy = $createdBy;
				
				if($existingEquipment->update())
				{
					$equipmentID = $existingEquipment->EquipmentID;
					$successFlag = 1;
				} else {
					throw BaseActiveController::modelValidationException($existingEquipment);
				}
			}
			//set response values
			$responseData = ['EquipmentID' => $equipmentID, 'EquipmentSerialNumber' => $equipmentSerialNumber, 'SuccessFlag' => $successFlag];
	
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->setStatusCode(201);
			$response->data = (object)$responseData;
			return $response;
		} catch(ForbiddenHttpException $e){
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
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
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionDeleteCalibration()
	{
		try
		{
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			
			//RBAC permissions check
			PermissionsController::requirePermission('equipmentCalibrationDelete', $client);
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true);
			//create response format
			$responseData = [];
			
			BaseActiveController::archiveJson($body, 'DeletedCalibration', BaseActiveController::getClientUser($client)->UserID, $client);
			
			//count number of items to delete
			$deletedRecords = $data['DeletedCalibration'];
			$deletedCount = count($deletedRecords);
			
			//loop records to be marked deleted
			for($i = 0; $i < $deletedCount; $i++)
			{
				//try catch to log individual errors
				try
				{	
					$successFlag = 0;
					$calibration = Calibration::find()
						->where(['ID' => $deletedRecords[$i]['ID']])
						->andWhere(['<>', 'DeletedFlag', 1])
						->one();
					if($calibration != null)
					{
						$calibration->DeletedFlag = 1;
						if($calibration->update())
						{
							$successFlag = 1;
						}
						else
						{
							throw BaseActiveController::modelValidationException($calibration);
						}
					}
					else{
						$successFlag = 1;
					}
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $deletedRecords[$i]);
				}
				$responseData['DeletedCalibration'][] = ['ID' => $deletedRecords[$i]['ID'], 'SuccessFlag' => $successFlag];
			}
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            BaseActiveController::logError($e, 'Forbidden http exception');
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
}