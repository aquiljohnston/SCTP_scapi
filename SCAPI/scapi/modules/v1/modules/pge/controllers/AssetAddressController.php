<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
//models for Address, Inspection, CGI, AOC, and Indication
use app\modules\v1\modules\pge\models\AssetAddress;
use app\modules\v1\modules\pge\models\AssetAddressAOC;
use app\modules\v1\modules\pge\models\AssetAddressCGE;
use app\modules\v1\modules\pge\models\AssetAddressIndication;
use app\modules\v1\modules\pge\models\AssetAddressInspection;
use app\modules\v1\modules\pge\models\AssetInspection;
use app\modules\v1\modules\pge\models\MasterLeakLog;

//The Asset Address Controller recives a json from the activity controller along with the current client(for db target), a userUID of the user who made the request, and
//an activityUID of the associated activity. It then checks the update flag of the asset address to deterine which function to send the data to. 
//If the update flag is not present it sent to the create function where it parses out each piece of he array: asset address, inspection, indications,
//aocs, and cgi. It then stores this data into the appropriate tables on the database.  
//If the update flag is present it is sent to the update function where it also parses the array and checks for an update flag for each piece if the update flag is not present
//it is ignored if the update flag is present it is processed. On an update the previous record is retrieved it's active flag is set to 0. A new record is created with 
//the data from the json the revision flag is incremented from the revision of the previous record and saved to the database.

class AssetAddressController extends Controller
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
                ],
            ];
        return $behaviors;
    }
	
	//TODO break up code into seperate functions

    public static function assetAddressParse($assetAddressArray, $client, $userUID, $ActivityUID, $activityLat, $activityLong)
    {
        try {
            //set db target
            BaseActiveRecord::setClient($client);
			
            //get asset and asset inspection UIDs from asset inspection with matching IRUID
            $assetInspection = AssetInspection::find()
                ->where(['InspectionRequestUID' => $assetAddressArray['General']['InspectionRequestUID']])
                ->one();
				
            //populate general variables
			$generalVariables['AssetAddressUID'] = $assetAddressArray['AssetAddressUID'];
            $generalVariables['AssetUID'] = $assetInspection->AssetUID;
            $generalVariables['AssetInspectionUID'] = $assetInspection->AssetInspectionUID;
            $generalVariables['MapGridUID'] = $assetAddressArray['General']['MapGridUID'];
            $generalVariables['MasterLeakLogUID'] = $assetAddressArray['General']['MasterLeakLogUID'];
            $generalVariables['InspectionRequestUID'] = $assetAddressArray['General']['InspectionRequestUID'];
			$generalVariables['CreatedUserUID'] = $userUID;
			$generalVariables['ModifiedUserUID'] = $userUID;
			$generalVariables['ActivityUID'] = $ActivityUID;

            //AssetAddress
			//flag indicating success of saving asset address record
			$assetAddressSuccessFlag = 0;
            //get previous record
            $previousAddress = AssetAddress::find()
                ->where(['AssetAddressUID' => $generalVariables['AssetAddressUID']])
                ->andWhere(['ActiveFlag' => 1])
                ->one();
			
			//check for 0 or empty lat/long
			//checkLatLong(assetDataToCheck, Lat, Long)
			$latLongValues = self::checkLatLong($assetAddressArray, $activityLat, $activityLong);
			$assetAddressArray['Latitude'] = $latLongValues['Latitude'];
			$assetAddressArray['Longitude'] = $latLongValues['Longitude'];			
			
			if($previousAddress == null)
			{			
				//new AssetAddress model
				$assetAddress = self::createAssetAddress($assetAddressArray, $generalVariables);
				try{
					//save model
					if ($assetAddress->save()){
						$assetAddressSuccessFlag = 1;
					}
				}
				catch(yii\db\Exception $e)
				{
					$assetAddressSuccessFlag = 1;
				}
			}
			else
			{	
				//update active flag
				$previousAddress->ActiveFlag = 0;
				//increment revision
				$addressRevision = $previousAddress->Revision + 1;
				//update
				if ($previousAddress->update()) {
					//if update succeeds create new record
					//convert model to an array so if can be passed to new model population method
					$previousAddressArray = $previousAddress->toArray();
					//populate additional fields
					$previousAddressArray['ModifiedUserUID'] = $userUID;
					$previousAddressArray['ActivityUID'] = $generalVariables['ActivityUID'];
					//new AssetAddress model
					$newAddress = self::createAssetAddress($assetAddressArray, $previousAddressArray, $addressRevision);					
					try{
						if ($newAddress->save()) {
							$assetAddressSuccessFlag = 1;
						}	
					}
					catch(yii\db\Exception $e)
					{
						$assetAddressSuccessFlag = 1;
					}
				}
			}	
			//save address
			if ($assetAddressSuccessFlag) {
				//create response array
				$savedData = ['AssetAddressUID' => $assetAddressArray['AssetAddressUID'], 'SuccessFlag' => $assetAddressSuccessFlag];
				//Inspection
				if ($assetAddressArray['Inspection'] != null) {
					//flag indicating success of saving inspection record
					$inspectionSuccessFlag = 0;
					//check for 0 or empty lat/long
					$latLongValues = self::checkLatLong($assetAddressArray['Inspection'], $activityLat, $activityLong);
					$assetAddressArray['Inspection']['Latitude'] = $latLongValues['Latitude'];
					$assetAddressArray['Inspection']['Longitude'] = $latLongValues['Longitude'];	
					//get previous record
					$previousInspection = AssetAddressInspection::find()
						->where(['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID']])
						->andWhere(['ActiveFlag' => 1])
						->one();
					if ($previousInspection != null) {
						//update active flag
						$previousInspection->ActiveFlag = 0;
						//increment revision
						$inspectionRevision = $previousInspection->Revision + 1;
						//update
						if ($previousInspection->update()) {
							//if update succeeds create new record
							//convert model to an array so if can be passed to new model population method
							$previousInspectionArray = $previousInspection->toArray();
							//populate additional fields
							$previousInspectionArray['AssetAddressUID'] = $generalVariables['AssetAddressUID'];
							$previousInspectionArray['ModifiedUserUID'] = $userUID;
							$previousInspectionArray['ActivityUID'] = $generalVariables['ActivityUID'];
							//new inspection model							
							$newInspection = self::createInspection($assetAddressArray['Inspection'], $previousInspectionArray, $inspectionRevision);
							try{
								if ($newInspection->save()) {
									$inspectionSuccessFlag = 1;
								}
							}
							catch(yii\db\Exception $e)
							{
								$inspectionSuccessFlag = 1;
							}
						}
					} else {
						//new inspection model
						$inspection = self::createInspection($assetAddressArray['Inspection'], $generalVariables);
						try{
							if ($inspection->save()) {
								$inspectionSuccessFlag = 1;
							}
						}
						catch(yii\db\Exception $e)
						{
							$inspectionSuccessFlag = 1;
						}
					}
					$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => $inspectionSuccessFlag];
				}
				//CGI
				if ($assetAddressArray['CGI'] != null) {
					//flag indicating success of saving CGI record
					$cgiSuccessFlag = 0;
					//check for 0 or empty lat/long
					$latLongValues = self::checkLatLong($assetAddressArray['CGI'], $activityLat, $activityLong);
					$assetAddressArray['CGI']['Latitude'] = $latLongValues['Latitude'];
					$assetAddressArray['CGI']['Longitude'] = $latLongValues['Longitude'];	
					//get previous record
					$previousCGI = AssetAddressCGE::find()
						->where(['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID']])
						->andWhere(['ActiveFlag' => 1])
						->one();
					if ($previousCGI != null) {
						//update active flag
						$previousCGI->ActiveFlag = 0;
						//increment revision
						$cgiRevision = $previousCGI->Revision + 1;
						//update
						if ($previousCGI->update()) {
							//if update succeeds create new record
							//convert model to an array so if can be passed to new model population method
							$previousCGIArray = $previousCGI->toArray();
							//populate additional fields
							$previousCGIArray['AssetAddressUID'] = $generalVariables['AssetAddressUID'];
							$previousCGIArray['ModifiedUserUID'] = $userUID;
							$previousCGIArray['ActivityUID'] = $generalVariables['ActivityUID'];
							//new inspection model
							$newCGI = self::createCGE($assetAddressArray['CGI'], $previousCGIArray, $cgiRevision);
							try{
								//save model
								if ($newCGI->save()) {
									//add to response array
									$cgiSuccessFlag = 1;
								}
							}
							catch(yii\db\Exception $e)
							{
								$cgiSuccessFlag = 1;
							}
						}
					} else {
						//new CGI model
						$newCGI = self::createCGE($assetAddressArray['CGI'], $generalVariables);
						try{
							//save model
							if ($newCGI->save()) {
								//add to response array
								$cgiSuccessFlag = 1;
							}
						}
						catch(yii\db\Exception $e)
						{
							$cgiSuccessFlag = 1;
						}
					}
					$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => $cgiSuccessFlag];
				}
				//AOCs
				if ($assetAddressArray['AOCs'] != null) {					
					$savedData['AOCs'] = [];
					//loop AOCs
					$AOCCount = (count($assetAddressArray['AOCs']));
					for ($i = 0; $i < $AOCCount; $i++) {
						//flag indicating success of saving aoc record
						$aocSuccessFlag = 0;
						//check for 0 or empty lat/long
						$latLongValues = self::checkLatLong($assetAddressArray['AOCs'][$i], $activityLat, $activityLong);
						$assetAddressArray['AOCs'][$i]['Latitude'] = $latLongValues['Latitude'];
						$assetAddressArray['AOCs'][$i]['Longitude'] = $latLongValues['Longitude'];	
						//get previous record
						$previousAOC = AssetAddressAOC::find()
							->where(['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID']])
							->andWhere(['ActiveFlag' => 1])
							->one();
						if ($previousAOC != null) {
							//update active flag
							$previousAOC->ActiveFlag = 0;
							//increment revision
							$aocRevision = $previousAOC->Revision + 1;
							//update
							if ($previousAOC->update()) {
								//if update succeeds create new record
								//convert model to an array so if can be passed to new model population method
								$previousAOCArray = $previousAOC->toArray();
								//populate additional fields
								$previousAOCArray['AssetAddressUID'] = $generalVariables['AssetAddressUID'];
								$previousAOCArray['ModifiedUserUID'] = $userUID;
								$previousAOCArray['ActivityUID'] = $generalVariables['ActivityUID'];
								//new AOC model
								$newAOC = self::createAOC($assetAddressArray['AOCs'][$i], $previousAOCArray, $aocRevision);
								try{
									//save model
									if ($newAOC->save()) {
										$aocSuccessFlag = 1;
									}
								}
								catch(yii\db\Exception $e)
								{
									$aocSuccessFlag = 1;
								}
							}
						} else {
							//new AOC model
							$AOC = self::createAOC($assetAddressArray['AOCs'][$i], $generalVariables);
							//check for sql constraint error
							try{
								//save model
								if ($AOC->save()) {
									$aocSuccessFlag = 1;
								}
							}
							catch(yii\db\Exception $e)
							{
								$aocSuccessFlag = 1;
							}
						}
						$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => $aocSuccessFlag];
					}
				}
				//Indications
				if ($assetAddressArray['Indications'] != null) {
					$savedData['Indications'] = [];
					//loop indications
					$IndicationCount = (count($assetAddressArray['Indications']));
					for ($i = 0; $i < $IndicationCount; $i++) {
						//flag indicating success of saving indication record
						$indicationSuccessFlag = 0;
						//check for 0 or empty lat/long
						$latLongValues = self::checkLatLong($assetAddressArray['Indications'][$i], $activityLat, $activityLong);
						$assetAddressArray['Indications'][$i]['Latitude'] = $latLongValues['Latitude'];
						$assetAddressArray['Indications'][$i]['Longitude'] = $latLongValues['Longitude'];	
						$previousIndication = AssetAddressIndication::find()
							->where(['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID']])
							->andWhere(['ActiveFlag' => 1])
							->one();
						if ($previousIndication != null) {
							$previousStatus = $previousIndication->StatusType;
							if ($previousStatus == 'In Progress' || $previousStatus == 'Pending') {
								//update active flag
								$previousIndication->ActiveFlag = 0;
								//increment revision
								$indicationRevision = $previousIndication->Revision + 1;
								if ($previousIndication->update()) {
									//if update succeeds create new record
									//convert model to an array so if can be passed to new model population method
									$previousIndicationArray = $previousIndication->toArray();
									//populate additional fields
									$previousIndicationArray['AssetAddressUID'] = $generalVariables['AssetAddressUID'];
									$previousIndicationArray['ModifiedUserUID'] = $userUID;
									$previousIndicationArray['ActivityUID'] = $generalVariables['ActivityUID'];
									//new indication model
									$newIndication = self::createIndication($assetAddressArray['Indications'][$i], $previousIndicationArray, $indicationRevision, $previousStatus, true);
									try{
										//save model
										if ($newIndication->save()) {
											//TODO determine if we need to call master leak log update here
											//was previously commented out in this location
											//need to discusss with Gary Wheeler
											$indicationSuccessFlag = 1;
										}
									}
									catch(yii\db\Exception $e)
									{
										$indicationSuccessFlag = 1;
									}
								}
							} else {
								//status was completed, no update can be performed
								$indicationSuccessFlag = 1;
							}
						} else {
							//new Indication model
							$indication = self::createIndication($assetAddressArray['Indications'][$i], $generalVariables);
							try{
								//save model
								if ($indication->save()) {
									//update map stamp to 'Not Approved' if neccessary
									$indicationSuccessFlag = self::updateMasterLeakLog($generalVariables['MasterLeakLogUID'], $userUID);
								}
							}
							catch(yii\db\Exception $e)
							{
								$indicationSuccessFlag = 1;
							}
						}
						$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => $indicationSuccessFlag];
					}
				}
				return $savedData;
			} else return ['AssetAddressUID' => $assetAddressArray['AssetAddressUID'], 'SuccessFlag' => $assetAddressSuccessFlag];
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
	
	//helper method used to check for 0 or null lat/long values
	private static function checkLatLong($dataArray, $lat, $long)
	{
		//create response variable
		$responseArray['Latitude'] = ""; 
		$responseArray['Longitude'] = "";
		//handle lat
		if (array_key_exists('Latitude', $dataArray))
		{
			if ($dataArray['Latitude'] == 0 || $dataArray['Latitude'] == null)
			{
				$responseArray['Latitude'] = $lat; 
			}
			else
			{
				$responseArray['Latitude'] = $dataArray['Latitude'];
			}
		}
		else
		{
			$responseArray['Latitude'] = $lat; 
		}
		//handle long
		if (array_key_exists('Longitude', $dataArray))
		{
			if ($dataArray['Longitude'] == 0 || $dataArray['Longitude'] == null)
			{
				$responseArray['Longitude'] = $long; 
			}
			else
			{
				$responseArray['Longitude'] = $dataArray['Longitude'];
			}
		}
		else
		{
			$responseArray['Longitude'] = $long; 
		}
		
		return $responseArray;
	}
	
	//helper method to create new Asset Address models
	private static function createAssetAddress($dataArray, $additionalData, $revision = 0)
	{
		//new AssetAddress model
		$assetAddress = new AssetAddress();
		//pass data to model
		$assetAddress->attributes = $dataArray;
		//additionalData
		$assetAddress->AssetUID = $additionalData['AssetUID'];                           //previous
		$assetAddress->AssetInspectionUID = $additionalData['AssetInspectionUID'];       //previous
		$assetAddress->MapGridUID = $additionalData['MapGridUID'];                       //previous
		$assetAddress->MasterLeakLogUID = $additionalData['MasterLeakLogUID'];           //previous
		$assetAddress->InspectionRequestUID = $additionalData['InspectionRequestUID'];   //previous
		$assetAddress->CreatedUserUID = $additionalData['CreatedUserUID'];               //previous
		$assetAddress->ModifiedUserUID = $additionalData['ModifiedUserUID'];                     //from general variables
		$assetAddress->ActivityUID = $additionalData['ActivityUID'];                     //from general variables
		$assetAddress->SrcOpenDTLT = $assetAddress->SrcDTLT;
		$assetAddress->Revision = $revision;
		return $assetAddress;
	}
	
	//helper method to create new Inspection models
	private static function createInspection($dataArray, $additionalData, $revision = 0)
	{
		//new inspection model
		$inspection = new AssetAddressInspection();
		//pass data to model
		$inspection->attributes = $dataArray;
		//additional fields
		$inspection->AssetAddressUID = $additionalData['AssetAddressUID']; //from general variables
		$inspection->AssetInspectionUID = $additionalData['AssetInspectionUID'];
		$inspection->MapGridUID = $additionalData['MapGridUID'];
		$inspection->MasterLeakLogUID = $additionalData['MasterLeakLogUID'];
		$inspection->InspectionRequestUID = $additionalData['InspectionRequestUID'];
		$inspection->CreatedUserUID = $additionalData['CreatedUserUID'];
		$inspection->ModifiedUserUID = $additionalData['ModifiedUserUID']; //from general variables
		$inspection->ActivityUID = $additionalData['ActivityUID']; //from general variables
		$inspection->SrcOpenDTLT = $inspection->SrcDTLT;
		$inspection->Revision = $revision;
		$inspection->ActiveFlag = 1;
		return $inspection;
	}
	
	//helper method to create new CGE models
	private static function createCGE($dataArray, $additionalData, $revision = 0)
	{
		//new CGE model
		$cgi = new AssetAddressCGE();
		//pass data to model
		$cgi->attributes = $dataArray;
		//additional fields
		$cgi->AssetAddressUID = $additionalData['AssetAddressUID']; //from general variables
		$cgi->AssetInspectionUID = $additionalData['AssetInspectionUID'];
		$cgi->MapGridUID = $additionalData['MapGridUID'];
		$cgi->MasterLeakLogUID = $additionalData['MasterLeakLogUID'];
		$cgi->InspectionRequestUID = $additionalData['InspectionRequestUID'];
		$cgi->CreatedUserUID = $additionalData['CreatedUserUID'];
		$cgi->ModifiedUserUID = $additionalData['ModifiedUserUID']; //from general variables
		$cgi->ActivityUID = $additionalData['ActivityUID']; //from general variables
		$cgi->SrcOpenDTLT = $cgi->SrcDTLT;
		$cgi->Revision = $revision;
		return $cgi;
	}
	
	//helper method to create new AOC models
	private static function createAOC($dataArray, $additionalData, $revision = 0)
	{
		//new AOC model
		$aoc = new AssetAddressAOC();
		//pass data to model
		$aoc->attributes = $dataArray;
		//additional fields
		$aoc->AssetAddressUID = $additionalData['AssetAddressUID']; //from general variables
		$aoc->AssetInspectionUID = $additionalData['AssetInspectionUID'];
		$aoc->MapGridUID = $additionalData['MapGridUID'];
		$aoc->MasterLeakLogUID = $additionalData['MasterLeakLogUID'];
		$aoc->InspectionRequestUID = $additionalData['InspectionRequestUID'];
		$aoc->CreatedUserUID = $additionalData['CreatedUserUID'];
		$aoc->ModifiedUserUID = $additionalData['ModifiedUserUID']; //from general variables
		$aoc->ActivityUID = $additionalData['ActivityUID']; //from general variables
		$aoc->SrcOpenDTLT = $aoc->SrcDTLT;
		$aoc->DateFound = $aoc->SrcDTLT;
		$aoc->Revision = $revision;
		$aoc->ActiveFlag = 1;
		return $aoc;
	}
	
	//helper method to create new Indication models
	private static function createIndication($dataArray, $additionalData, $revision = 0, $status = 'In Progress', $update = false)
	{
		//new Indication model
		$indication = new AssetAddressIndication();
		//pass data to model
		$indication->attributes = $dataArray;
		//additional fields
		$indication->AssetAddressUID = $additionalData['AssetAddressUID']; //from general variables
		$indication->AssetInspectionUID = $additionalData['AssetInspectionUID'];
		$indication->MapGridUID = $additionalData['MapGridUID'];
		$indication->MasterLeakLogUID = $additionalData['MasterLeakLogUID'];
		$indication->InspectionRequestUID = $additionalData['InspectionRequestUID'];
		$indication->CreatedUserUID = $additionalData['CreatedUserUID'];
		$indication->ModifiedUserUID = $additionalData['ModifiedUserUID']; //from general variables
		$indication->ActivityUID = $additionalData['ActivityUID']; //from general variables
		$indication->SrcOpenDTLT = $indication->SrcDTLT;
		$indication->FoundDateTime = $indication->SrcDTLT;
		$indication->Revision = $revision;
		if($indication->StatusType == null)
		{
			$indication->StatusType = $status;
		}
		//only individually set on an update otherwise not present in additionalData
		if($update)
		{
			$indication->EquipmentFoundByUID = $additionalData['EquipmentFoundByUID'];
			$indication->EquipmentGradeByUID = $additionalData['EquipmentGradeByUID'];
			$indication->SAPComments = $additionalData['SAPComments'];
			$indication->SAPNo = $additionalData['SAPNo'];
			$indication->LockedFlag = $additionalData['LockedFlag'];
		}
		return $indication;
	}
	
	//helper method to 
	private static function updateMasterLeakLog($masterLeakLogUID, $userUID)
	{
		//flag indicating success of saving master leak log record
		$successFlag = 0;
		//get most recent master leak log revision
		$previousMasterLeakLog = MasterLeakLog::find()
			->where(['MasterLeakLogUID' => $masterLeakLogUID])
			->andWhere(['ActiveFlag' => 1])
			->one();
		if ($previousMasterLeakLog->StatusType != 'Not Approved' && $previousMasterLeakLog->StatusType != 'InProgress') {
			//create new MasterLeakLog object
			$newMasterLeakLog = new MasterLeakLog;
			//pass old data to new model
			$newMasterLeakLog->MasterLeakLogUID = $previousMasterLeakLog->MasterLeakLogUID;
			$newMasterLeakLog->InspectionRequestLogUID = $previousMasterLeakLog->InspectionRequestLogUID;
			$newMasterLeakLog->MapGridUID = $previousMasterLeakLog->MapGridUID;
			$newMasterLeakLog->ServiceDate = $previousMasterLeakLog->ServiceDate;
			$newMasterLeakLog->CreatedUserUID = $previousMasterLeakLog->CreatedUserUID;
			//deactivate previous record
			$previousMasterLeakLog->ActiveFlag = 0;
			//get revision and increment
			$masterLeakLogRevision = $previousMasterLeakLog->Revision + 1;
			if ($previousMasterLeakLog->update()) {
				//set new record status type and revision
				$newMasterLeakLog->StatusType = 'Not Approved';
				$newMasterLeakLog->Revision = $masterLeakLogRevision;
				$newMasterLeakLog->ModifiedUserUID = $userUID;
				$newMasterLeakLog->SourceID = 'API';
				$newMasterLeakLog->SrcDTLT = BaseActiveController::getDate();
				$newMasterLeakLog->RevisionComments = 'New Indication Created, Leak Log Reset To "Not Approved".';
				if ($newMasterLeakLog->save()) {
					//add to response array
					$successFlag = 1;
				}
			}
		} else {
			//Master Leak Log does not require an update StatusType is 'Not Approved'
			$successFlag = 1;
		}
		return $successFlag;
	}
}