<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\controllers\ActivityController;
use app\modules\v1\modules\pge\controllers\AssetAddressController;
use app\modules\v1\modules\pge\controllers\WindSpeedController;
use app\modules\v1\modules\pge\controllers\EquipmentController;
use app\modules\v1\modules\pge\controllers\WorkQueueController;
use app\modules\v1\modules\pge\controllers\TaskOutController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class PgeActivityController extends ActivityController
{
    public $modelClass = 'app\modules\v1\models\Activity';
	
	/**
	* sets verb filters for http request
	* @return an array of behaviors
	*/
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
                ],  
            ];
		return $behaviors;	
	}

	/**
	 * Checks keys associated with pge activity types
	 * Send data to approriate functions for parsing
	 *
	 * @return \yii\console\Response|Response
	 * @throws \yii\web\HttpException
	 */
	public static function parseActivityData($activityData, $client, $createdBy, $activityUID)
	{		
		$responseData = [];
		
		//handle pge equipment calibration
		if (array_key_exists('EquipmentCalibration', $activityData))
		{
			//get activity uid
			$activityUID = $activityData['ActivityUID'];
			$savedEquipmentCalibrations = EquipmentController::calibrationParse($activityData['EquipmentCalibration'], $client, $createdBy, $activityUID);
			$responseData['EquipmentCalibration'] = $savedEquipmentCalibrations;
		}
		
		//handle pge lock work queue
		if (array_key_exists('WorkQueue', $activityData))
		{
			$lockedWorkQueue = WorkQueueController::lockRecords($activityData['WorkQueue'], $client, $createdBy);
			$responseData['WorkQueue'] = $lockedWorkQueue;
		}
		
		//handle pge wind speed entries
		if (array_key_exists('WindSpeed', $activityData))
		{
			$savedWindSpeed = WindSpeedController::create($activityData['WindSpeed'], $client, $createdBy);
			$responseData['WindSpeed'] = $savedWindSpeed;
		}
		
		//handle pge inspection
		if (array_key_exists('AssetAddress', $activityData))
		{
			$activityLat = 0;
			$activityLong = 0;
			//get activty lat/long to populate any potential blanks
			if(array_key_exists('ActivityLatitude', $activityData))
			{
				$activityLat = $activityData['ActivityLatitude'];
			}
			if(array_key_exists('ActivityLongitude', $activityData))
			{
				$activityLong =  $activityData['ActivityLongitude'];
			}
			$savedAssetAddress = AssetAddressController::assetAddressParse($activityData['AssetAddress'], $client, $createdBy, $activityUID, $activityLat, $activityLong);
			$responseData['AssetAddress'] = $savedAssetAddress;
		}
		
		//handle pge taskout
		if (array_key_exists('TaskOutMaps', $activityData)) {
			TaskOutController::processJSON(json_encode($activityData));
		}
		
		return $responseData;
	}
}