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

            $savedArray = [];

            //get asset and asset inspection UIDs from asset inspection with matching IRUID
            $assetInspection = AssetInspection::find()
                ->where(['InspectionRequestUID' => $assetAddressArray['General']['InspectionRequestUID']])
                ->one();

            //populate general variables
            $assetUID = $assetInspection->AssetUID;
            $assetInspectionUID = $assetInspection->AssetInspectionUID;
            $mapGridUID = $assetAddressArray['General']['MapGridUID'];
            $masterLeakLogUID = $assetAddressArray['General']['MasterLeakLogUID'];
            $assetAddressUID = $assetAddressArray['AssetAddressUID'];
            $inspectionRequestUID = $assetAddressArray['General']['InspectionRequestUID'];

            //Asset			
            //get previous record
            $previousAddress = AssetAddress::find()
                ->where(['AssetAddressUID' => $assetAddressUID])
                ->andWhere(['ActiveFlag' => 1])
                ->one();
			
			//assetSaveFlag
			$assetSavedFlag = false;
			
			//check for 0 or empty lat/long
			if (array_key_exists('Latitude', $assetAddressArray))
			{
				if ($assetAddressArray['Latitude'] == 0 || $assetAddressArray['Latitude'] == null)
				{
					$assetAddressArray['Latitude'] = $activityLat; 
				}
			}	
			else
			{
				$assetAddressArray['Latitude'] = $activityLat; 
			}
			
			if (array_key_exists('Longitude', $assetAddressArray))
			{
				if ($assetAddressArray['Longitude'] == 0 || $assetAddressArray['Longitude'] == null)
				{
					$assetAddressArray['Longitude'] = $activityLong; 
				}
			}
			else
			{
				$assetAddressArray['Longitude'] = $activityLong; 
			}
			

			if($previousAddress == null)
			{			
				//new AssetAddress model
				$assetAddress = new AssetAddress();
				//pass data to model
				$assetAddress->attributes = $assetAddressArray;
				//additional fields
				$assetAddress->AssetUID = $assetUID;
				$assetAddress->AssetInspectionUID = $assetInspectionUID;
				$assetAddress->MapGridUID = $mapGridUID;
				$assetAddress->MasterLeakLogUID = $masterLeakLogUID;
				$assetAddress->InspectionRequestUID = $inspectionRequestUID;
				$assetAddress->CreatedUserUID = $userUID;
				$assetAddress->ModifiedUserUID = $userUID;
				$assetAddress->ActivityUID = $ActivityUID;
				$assetAddress->SrcOpenDTLT = $assetAddress->SrcDTLT;

				try{
					//save model
					if ($assetAddress->save()){
						$assetSavedFlag = true;
					}
				}
				catch(yii\db\Exception $e)
				{
					$assetSavedFlag = true;
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
					//new AssetAddress model
					$newAddress = new AssetAddress();
					//pass data to model
					$newAddress->attributes = $assetAddressArray;
					//additional fields
					$newAddress->AssetUID = $assetUID;
					$newAddress->AssetInspectionUID = $assetInspectionUID;
					$newAddress->MapGridUID = $mapGridUID;
					$newAddress->MasterLeakLogUID = $masterLeakLogUID;
					$newAddress->InspectionRequestUID = $inspectionRequestUID;
					$newAddress->CreatedUserUID = $userUID;
					$newAddress->ModifiedUserUID = $userUID;
					$newAddress->ActivityUID = $ActivityUID;
					$newAddress->SrcOpenDTLT = $newAddress->SrcDTLT;
					$newAddress->Revision = $addressRevision;
					
					try{
						if ($newAddress->save()) {
							$assetSavedFlag = true;
						}	
					}
					catch(yii\db\Exception $e)
					{
						$assetSavedFlag = true;
					}
				}
			}
			
			//save address
			if ($assetSavedFlag) {
				//create response array
				$savedData = ['AssetAddressUID' => $assetAddressArray['AssetAddressUID'], 'SuccessFlag' => 1];

				//Inspection
				if ($assetAddressArray['Inspection'] != null) {
					
					//check for 0 or empty lat/long
					if (array_key_exists('Latitude', $assetAddressArray['Inspection']))
					{
						if ($assetAddressArray['Inspection']['Latitude'] == 0 || $assetAddressArray['Inspection']['Latitude'] == null)
						{
							$assetAddressArray['Inspection']['Latitude'] = $activityLat; 
						}
					}
					else
					{
						$assetAddressArray['Inspection']['Latitude'] = $activityLat;
					}
					
					if (array_key_exists('Longitude', $assetAddressArray['Inspection']))
					{
						if ($assetAddressArray['Inspection']['Longitude'] == 0 || $assetAddressArray['Inspection']['Longitude'] == null)
						{
							$assetAddressArray['Inspection']['Longitude'] = $activityLong; 
						}
					}
					else
					{
						$assetAddressArray['Inspection']['Longitude'] = $activityLong; 
					}
					
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
							//new inspection model
							$newInspection = new AssetAddressInspection();
							//pass data to model
							$newInspection->attributes = $assetAddressArray['Inspection'];
							//additional fields
							$newInspection->AssetAddressUID = $assetAddressUID;
							$newInspection->AssetInspectionUID = $assetInspectionUID;
							$newInspection->MapGridUID = $mapGridUID;
							$newInspection->MasterLeakLogUID = $masterLeakLogUID;
							$newInspection->InspectionRequestUID = $inspectionRequestUID;
							$newInspection->CreatedUserUID = $userUID;
							$newInspection->ModifiedUserUID = $userUID;
							$newInspection->ActivityUID = $ActivityUID;
							$newInspection->SrcOpenDTLT = $newInspection->SrcDTLT;
							$newInspection->Revision = $inspectionRevision;
							$newInspection->ActiveFlag = 1;

							try{
								if ($newInspection->save()) {
									$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => 1];
								} else {
									$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => 0];
								}
							}
							catch(yii\db\Exception $e)
							{
								$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => 1];
							}
						} else {
							$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => 0];

						}
					} else {
						//new inspection model
						$inspection = new AssetAddressInspection();
						//pass data to model
						$inspection->attributes = $assetAddressArray['Inspection'];
						//additional fields
						$inspection->AssetAddressUID = $assetAddressUID;
						$inspection->AssetInspectionUID = $assetInspectionUID;
						$inspection->MapGridUID = $mapGridUID;
						$inspection->MasterLeakLogUID = $masterLeakLogUID;
						$inspection->InspectionRequestUID = $inspectionRequestUID;
						$inspection->CreatedUserUID = $userUID;
						$inspection->ModifiedUserUID = $userUID;
						$inspection->ActivityUID = $ActivityUID;
						$inspection->SrcOpenDTLT = $inspection->SrcDTLT;

						try{
							if ($inspection->save()) {
								$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => 1];
							} else {
								$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => 0];
							}
						}
						catch(yii\db\Exception $e)
						{
							$savedData['Inspection'] = ['AssetAddressInspectionUID' => $assetAddressArray['Inspection']['AssetAddressInspectionUID'], 'SuccessFlag' => 1];
						}
					}
				}

				//CGI
				if ($assetAddressArray['CGI'] != null) {
					
					//check for 0 or empty lat/long
					if (array_key_exists('Latitude', $assetAddressArray['CGI']))
					{
						if ($assetAddressArray['CGI']['Latitude'] == 0 || $assetAddressArray['CGI']['Latitude'] == null)
						{
							$assetAddressArray['CGI']['Latitude'] = $activityLat; 
						}
					}
					else
					{
						$assetAddressArray['CGI']['Latitude'] = $activityLat; 
					}
					
					if (array_key_exists('Longitude', $assetAddressArray['CGI']))
					{
						if ($assetAddressArray['CGI']['Longitude'] == 0 || $assetAddressArray['CGI']['Longitude'] == null)
						{
							$assetAddressArray['CGI']['Longitude'] = $activityLong; 
						}
					}
					else
					{
						$assetAddressArray['CGI']['Longitude'] = $activityLong; 
					}
					
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
							//new CGI model
							$newCGI = new AssetAddressCGE();
							//pass data to model
							$newCGI->attributes = $assetAddressArray['CGI'];
							//additional fields
							$newCGI->AssetAddressUID = $assetAddressUID;
							$newCGI->AssetInspectionUID = $assetInspectionUID;
							$newCGI->MapGridUID = $mapGridUID;
							$newCGI->MasterLeakLogUID = $masterLeakLogUID;
							$newCGI->InspectionRequestUID = $inspectionRequestUID;
							$newCGI->CreatedUserUID = $userUID;
							$newCGI->ModifiedUserUID = $userUID;
							$newCGI->ActivityUID = $ActivityUID;
							$newCGI->SrcOpenDTLT = $newCGI->SrcDTLT;
							$newCGI->Revision = $cgiRevision;

							try{
								//save model
								if ($newCGI->save()) {
									//add to response array
									$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => 1];
								} else {
									$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => 0];
								}
							}
							catch(yii\db\Exception $e)
							{
								$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => 1];
							}
						} else {
							$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => 0];
						}
					} else {
						//new CGI model
						$newCGI = new AssetAddressCGE();
						//pass data to model
						$newCGI->attributes = $assetAddressArray['CGI'];
						//additional fields
						$newCGI->AssetAddressUID = $assetAddressUID;
						$newCGI->AssetInspectionUID = $assetInspectionUID;
						$newCGI->MapGridUID = $mapGridUID;
						$newCGI->MasterLeakLogUID = $masterLeakLogUID;
						$newCGI->InspectionRequestUID = $inspectionRequestUID;
						$newCGI->CreatedUserUID = $userUID;
						$newCGI->ModifiedUserUID = $userUID;
						$newCGI->ActivityUID = $ActivityUID;
						$newCGI->SrcOpenDTLT = $newCGI->SrcDTLT;

						try{
							//save model
							if ($newCGI->save()) {
								//add to response array
								$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => 1];
							} else {
								$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => 0];
							}
						}
						catch(yii\db\Exception $e)
						{
							$savedData['CGI'] = ['AssetAddressCGEUID' => $assetAddressArray['CGI']['AssetAddressCGEUID'], 'SuccessFlag' => 1];
						}
					}
				}

				//AOCs
				if ($assetAddressArray['AOCs'] != null) {					
					$savedData['AOCs'] = [];
					//loop AOCs
					$AOCCount = (count($assetAddressArray['AOCs']));
					for ($i = 0; $i < $AOCCount; $i++) {
						
						//check for 0 or empty lat/long
						if (array_key_exists('Latitude', $assetAddressArray['AOCs'][$i]))
						{
							if ($assetAddressArray['AOCs'][$i]['Latitude'] == 0 || $assetAddressArray['AOCs'][$i]['Latitude'] == null)
							{
								$assetAddressArray['AOCs'][$i]['Latitude'] = $activityLat; 
							}
						}
						else
						{
							$assetAddressArray['AOCs'][$i]['Latitude'] = $activityLat; 
						}
						
						if (array_key_exists('Longitude', $assetAddressArray['AOCs'][$i]))
						{
							if ($assetAddressArray['AOCs'][$i]['Longitude'] == 0 || $assetAddressArray['AOCs'][$i]['Longitude'] == null)
							{
								$assetAddressArray['AOCs'][$i]['Longitude'] = $activityLong; 
							}
						}
						else
						{
							$assetAddressArray['AOCs'][$i]['Longitude'] = $activityLong; 
						}
						
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
								//new AOC model
								$newAOC = new AssetAddressAOC();
								//pass data to model
								$newAOC->attributes = $assetAddressArray['AOCs'][$i];
								//additional fields
								$newAOC->AssetAddressUID = $assetAddressUID;
								$newAOC->AssetInspectionUID = $assetInspectionUID;
								$newAOC->MapGridUID = $mapGridUID;
								$newAOC->MasterLeakLogUID = $masterLeakLogUID;
								$newAOC->InspectionRequestUID = $inspectionRequestUID;
								$newAOC->CreatedUserUID = $userUID;
								$newAOC->ModifiedUserUID = $userUID;
								$newAOC->ActivityUID = $ActivityUID;
								$newAOC->SrcOpenDTLT = $newAOC->SrcDTLT;
								$newAOC->DateFound = $newAOC->SrcDTLT;
								$newAOC->Revision = $aocRevision;
								$newAOC->ActiveFlag = 1;
							
							try{
								//save model
								if ($newAOC->save()) {
									//add to response array
									$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => 1];
								} else {
									$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => 0];
								}
							}
							catch(yii\db\Exception $e)
							{
								$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => 1];
							}
							} else {
								$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => 0];
							}
						} else {
							//new AOC model
							$AOC = new AssetAddressAOC();
							//pass data to model
							$AOC->attributes = $assetAddressArray['AOCs'][$i];
							//additional fields
							$AOC->AssetAddressUID = $assetAddressUID;
							$AOC->AssetInspectionUID = $assetInspectionUID;
							$AOC->MapGridUID = $mapGridUID;
							$AOC->MasterLeakLogUID = $masterLeakLogUID;
							$AOC->InspectionRequestUID = $inspectionRequestUID;
							$AOC->CreatedUserUID = $userUID;
							$AOC->ModifiedUserUID = $userUID;
							$AOC->ActivityUID = $ActivityUID;
							$AOC->SrcOpenDTLT = $AOC->SrcDTLT;
							$AOC->DateFound = $AOC->SrcDTLT;
							
							//check for sql constraint error
							try{
								//save model
								if ($AOC->save()) {
									//add to response array
									$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => 1];
								} else {
									$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => 0];
								}
							}
							catch(yii\db\Exception $e)
							{
								$savedData['AOCs'][] = ['AssetAddressAOCUID' => $assetAddressArray['AOCs'][$i]['AssetAddressAOCUID'], 'SuccessFlag' => 1];
							}
						}
					}
				}

				//Indications
				if ($assetAddressArray['Indications'] != null) {
					$savedData['Indications'] = [];
					//loop indications
					$IndicationCount = (count($assetAddressArray['Indications']));
					for ($i = 0; $i < $IndicationCount; $i++) {
						
						//check for 0 or empty lat/long
						if (array_key_exists('Latitude', $assetAddressArray['Indications'][$i]))
						{
							if ($assetAddressArray['Indications'][$i]['Latitude'] == 0 || $assetAddressArray['Indications'][$i]['Latitude'] == null)
							{
								$assetAddressArray['Indications'][$i]['Latitude'] = $activityLat; 
							}
						}
						else
						{
							$assetAddressArray['Indications'][$i]['Latitude'] = $activityLat; 
						}
						
						if (array_key_exists('Longitude', $assetAddressArray['Indications'][$i]))
						{
							if ($assetAddressArray['Indications'][$i]['Longitude'] == 0 || $assetAddressArray['Indications'][$i]['Longitude'] == null)
							{
								$assetAddressArray['Indications'][$i]['Longitude'] = $activityLong; 
							}
						}
						else
						{
							$assetAddressArray['Indications'][$i]['Longitude'] = $activityLong; 
						}
						
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
									//new Indication model
									$newIndication = new AssetAddressIndication();
									//pass data to model
									$newIndication->attributes = $assetAddressArray['Indications'][$i];
									//additional fields
									$newIndication->AssetAddressUID = $assetAddressUID;
									$newIndication->AssetInspectionUID = $assetInspectionUID;
									$newIndication->MapGridUID = $mapGridUID;
									$newIndication->MasterLeakLogUID = $masterLeakLogUID;
									$newIndication->InspectionRequestUID = $inspectionRequestUID;
									$newIndication->CreatedUserUID = $userUID;
									$newIndication->ModifiedUserUID = $userUID;
									$newIndication->ActivityUID = $ActivityUID;
									$newIndication->SrcOpenDTLT = $newIndication->SrcDTLT;
									$newIndication->FoundDateTime = $newIndication->SrcDTLT;
									$newIndication->Revision = $indicationRevision;
									if ($newIndication->StatusType == null)
									{
										$newIndication->StatusType = $previousStatus;
									}
									$newIndication->LockedFlag = $previousIndication->LockedFlag;
									//$newIndication->ActiveFlag = 1;
									
									try{
										//save model
										if ($newIndication->save()) {
											/*//updateMasterLeakLog StatusType to 'Not Approved'
											//get most recent master leak log revision
											$previousMasterLeakLog = MasterLeakLog::find()
												->where(['MasterLeakLogUID' => $masterLeakLogUID])
												->andWhere(['ActiveFlag' => 1])
												->one();*/

	//                                            /*if ($previousMasterLeakLog->StatusType != 'Not Approved' && $previousMasterLeakLog->StatusType != 'InProgress') {
	//                                                //create new MasterLeakLog object
	//                                                $newMasterLeakLog = new MasterLeakLog;
	//                                                //pass old data to new model
	//                                                $newMasterLeakLog->MasterLeakLogUID = $previousMasterLeakLog->MasterLeakLogUID;
	//                                                $newMasterLeakLog->InspectionRequestLogUID = $previousMasterLeakLog->InspectionRequestLogUID;
	//                                                $newMasterLeakLog->MapGridUID = $previousMasterLeakLog->MapGridUID;
	//                                                $newMasterLeakLog->ServiceDate = $previousMasterLeakLog->ServiceDate;
	//                                                $newMasterLeakLog->CreatedUserUID = $previousMasterLeakLog->CreatedUserUID;
	//                                                //deactivate previous record
	//                                                $previousMasterLeakLog->ActiveFlag = 0;
	//                                                //get revision and increment
	//                                                $masterLeakLogRevision = $previousMasterLeakLog->Revision + 1;
	//                                                if ($previousMasterLeakLog->update()) {
	//                                                    //set new record status type and revision
	//                                                    $newMasterLeakLog->StatusType = 'Not Approved';
	//                                                    $newMasterLeakLog->Revision = $masterLeakLogRevision;
	//                                                    $newMasterLeakLog->ModifiedUserUID = $userUID;
	//                                                    $newMasterLeakLog->SourceID = 'API';
	//                                                    $newMasterLeakLog->SrcDTLT = BaseActiveController::getDate();
	//                                                    $newMasterLeakLog->RevisionComments = 'Indication Updated, Leak Log Reset To "Not Approved".';
	//                                                    if ($newMasterLeakLog->save()) {
	//                                                        //add to response array
	//                                                        $savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
	//                                                    } else {
	//                                                        //new master leak log failed to save
	//                                                        $savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 0];
	//                                                    }
	//                                                } else {
	//                                                    //previous master leak log failed to update
	//                                                    $savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 0];
	//                                                }
	//                                            } else {
	//                                                //master leak log does not require an update StatusType is Not Approved
	//                                                $savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
	//                                            }*/
											$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
										} else {
											//new indication failed to save
											$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 0];
										}
									}
									catch(yii\db\Exception $e)
									{
										$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
									}
								} else {
									//previous indication failed to update
									$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 0];
								}
							} else {
								//status was completed no update can be performed
								$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
							}
						} else {

							//new Indication model
							$indication = new AssetAddressIndication();
							//pass data to model
							$indication->attributes = $assetAddressArray['Indications'][$i];
							//additional fields
							$indication->AssetAddressUID = $assetAddressUID;
							$indication->AssetInspectionUID = $assetInspectionUID;
							$indication->MapGridUID = $mapGridUID;
							$indication->MasterLeakLogUID = $masterLeakLogUID;
							$indication->InspectionRequestUID = $inspectionRequestUID;
							$indication->CreatedUserUID = $userUID;
							$indication->ModifiedUserUID = $userUID;
							$indication->ActivityUID = $ActivityUID;
							$indication->SrcOpenDTLT = $indication->SrcDTLT;
							$indication->FoundDateTime = $indication->SrcDTLT;
							if ($indication->StatusType == null)
							{
								$indication->StatusType = 'In Progress';
							}

							try{
								//save model
								if ($indication->save()) {
									//TODO: Move functionality for Master Leak Log Update into seperate function updateMasterLeakLog(), call in both create and update

									//update map stamp to 'Not Approved' if neccessary
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
												$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
											} else {
												//new master leak log failed to save
												$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 0];
											}
										} else {
											//previous master leak log failed to update
											$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 0];
										}
									} else {
										//Master Leak Log does not require an update StatusType is 'Not Approved'
										$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
									}
								} else {
									//failed to save indication
									$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 0];
								}
							}
							catch(yii\db\Exception $e)
							{
								$savedData['Indications'][] = ['AssetAddressIndicationUID' => $assetAddressArray['Indications'][$i]['AssetAddressIndicationUID'], 'SuccessFlag' => 1];
							}
						}
					}
				}
				return $savedData;
			} else return ['AssetAddressUID' => $assetAddressArray['AssetAddressUID'], 'SuccessFlag' => 0];
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
}