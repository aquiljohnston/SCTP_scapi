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
	
	//Parses Asset Address Array from the activity json and stores data into the appropriate tables.
	public static function create($assetAddressArray, $client, $userUID, $ActivityUID)
	{
		try
		{
			//set db target
			BaseActiveRecord::setClient($client);
			
			$savedArray = [];
			
			//get asset and asset inspection UIDs from asset inspection with matching IRUID
			$assetInspection = AssetInspection::find()
				->where(['InspectionRequestUID'=>$assetAddressArray["General"]["InspectionRequestUID"]])
				->one();
			
			Yii::trace("Inspection Request UID: " . $assetAddressArray["General"]["InspectionRequestUID"]);
			Yii::trace("Asset Inspection: " . json_encode($assetInspection->attributes));
			
			//populate general variables
			$assetUID = $assetInspection->AssetUID;
			$assetInspectionUID = $assetInspection->AssetInspectionUID;
			$mapGridUID = $assetAddressArray["General"]["MapGridUID"];
			$masterLeakLogUID = $assetAddressArray["General"]["MasterLeakLogUID"];
			$assetAddressUID = $assetAddressArray["AssetAddressUID"];
			$inspectionRequestUID = $assetAddressArray["General"]["InspectionRequestUID"];
			
			//Asset
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
			
			Yii::trace("Asset Address: " . json_encode($assetAddress->attributes));
			
			//save model
			if($assetAddress->save())
			{
				//create response array
				$savedData = $assetAddress->toArray();
				
				//Inspection
				if($assetAddressArray["Inspection"] != null)
				{
					//new inspection model
					$inspection = new AssetAddressInspection();
					//pass data to model
					$inspection->attributes = $assetAddressArray["Inspection"];
					//additional fields
					$inspection->AssetAddressUID = $assetAddressUID;
					$inspection->AssetInspectionUID = $assetInspectionUID;
					$inspection->MapGridUID = $mapGridUID;
					$inspection->MasterLeakLogUID = $masterLeakLogUID;
					$inspection->InspectionRequestUID = $inspectionRequestUID;
					$inspection->CreatedUserUID = $userUID;
					$inspection->ModifiedUserUID = $userUID;
					$inspection->ActivityUID = $ActivityUID;
					$inspection->SrcOpenDTLT = $inspection->srcDTLT;
					
					if($inspection->save())
					{
						$savedData["Inspection"] = $inspection;
					}
					else
					{
						$savedData["Inspection"] = 'Failed to Save Inspection Record';
					}
				}
				
				//CGI
				if($assetAddressArray["CGI"] != null)
				{
					//new CGI model
					$cgi = new AssetAddressCGE();
					//pass data to model
					$cgi->attributes = $assetAddressArray["CGI"];
					//additional fields
					$cgi->AssetAddressUID = $assetAddressUID;
					$cgi->AssetInspectionUID = $assetInspectionUID;
					$cgi->MapGridUID = $mapGridUID;
					$cgi->MasterLeakLogUID = $masterLeakLogUID;
					$cgi->InspectionRequestUID = $inspectionRequestUID;
					$cgi->CreatedUserUID = $userUID;
					$cgi->ModifiedUserUID = $userUID;
					$cgi->ActivityUID = $ActivityUID;
					$cgi->SrcOpenDTLT = $cgi->SrcDTLT;
					
					Yii::trace("Asset Address CGI: " . json_encode($cgi->attributes));
					
					//save model
					if($cgi->save())
					{
						//add to response array
						$savedData["CGI"] = $cgi;
					}
					else
					{
						$savedData["CGI"] = 'Failed to Save CGI Record';
					}
				}
				//AOCs
				if($assetAddressArray["AOCs"] != null)
				{
					$savedData["AOCs"] = [];
					//loop AOCs
					$AOCCount = (count($assetAddressArray["AOCs"]));
					for ($i = 0; $i < $AOCCount; $i++)
					{
						//new AOC model
						$AOC = new AssetAddressAOC();
						//pass data to model
						$AOC->attributes = $assetAddressArray["AOCs"][$i];
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
						
						//save model
						if($AOC->save())
						{
							//add to response array
							$savedData["AOCs"][] = $AOC;
						}
						else
						{
							$savedData["AOCs"][] = 'Failed to Save AOC Record';
						}
					}
					
				}
				//Indications
				if($assetAddressArray["Indications"] != null)
				{
					$savedData["Indications"] = [];
					//loop indications
					$IndicationCount = (count($assetAddressArray["Indications"]));
					for ($i = 0; $i < $IndicationCount; $i++)
					{
						//new Indication model
						$indication = new AssetAddressIndication();
						//pass data to model
						$indication->attributes = $assetAddressArray["Indications"][$i];
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
						
						//save model
						if($indication->save())
						{
							//add to response array
							$savedData["Indications"][] = $indication;
						}
						else
						{
							$savedData["Indications"][] = 'Failed to Save Indication Record';
						}
					}
				}
				return $savedData;
			}
			else return null;			
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