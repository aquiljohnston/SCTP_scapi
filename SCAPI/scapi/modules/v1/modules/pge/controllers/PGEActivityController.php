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
//need to use models for Inspection, CGI, AOC, and Indication
use app\modules\v1\modules\pge\models\AddressAssetAOC;
use app\modules\v1\modules\pge\models\Asset;
use app\modules\v1\modules\pge\models\AssetAddress;
use app\modules\v1\modules\pge\models\AssetAddressCGE;
use app\modules\v1\modules\pge\models\AssetAddressIndication;
use app\modules\v1\modules\pge\models\AssetAddressInspection;
use app\modules\v1\modules\pge\models\AssetInspection;

class PGEActivityController extends Controller 
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
	
	//Parses Inspection Array from the activity json and stores data into the appropriate tables.
	public static function create($inspectionArray, $activityUID, $client)
	{
		try
		{
			//set db target
			BaseActiveRecord::setClient($client);
				
			//TODO ensure that all records are recieving the necessary UIDs from associated data
			//Missing multiple UIDs need to discuss where missing UIDs will be coming from
			//Missing UIDs: AssetAddress, Asset
			
			$savedArray = [];
			
			//Inspection
			//new inspection model
			$inspection = new AssetInspection();
			//pass data to model
			$inspection->attributes = $inspectionArray;
			//additional fields
			
			//save model
			if($inspection->save())
			{
				//create response array
				$inspection->toArray();
				//Asset
				if($inspectionArray["Asset"] != null])
				{
					//new Asset model
					$asset = new Asset();
					$assetAddress = new AssetAddress();
					//pass data to model
					$asset->attributes = $inspectionArray["Asset"];
					$assetAddress->attributes = $inspectionArray["Asset"];
					//additional fields
					
					//save model
					$asset->save();
					$assetAddress->save();
					//add to response array
					$inspection["Asset"] = $asset;
					$inspection["AssetAddress"] = $assetAddress;
				}
				
				//CGI
				if($inspectionArray["CGI"] != null)
				{
					//new CGI model
					$cgi = new AssetAddressCGE();
					//pass data to model
					$cgi->attributes = $inspectionArray["CGI"];
					//additional fields
					
					//save model
					$cgi->save();
					//add to response array
					$inspection["CGI"] = $cgi;
				}
				//AOCs
				if($inspectionArray["AOCs"] != null)
				{
					$inspection["AOCs"] = [];
					//loop AOCs
					$AOCCount = (count($inspectionArray["AOCs"]));
					for ($i = 0; $i < $AOCCount; $i++)
					{
						//new AOC model
						$AOC = new AddressAssetAOC();
						//pass data to model
						$AOC->attributes = $inspectionArray["AOCs"][$i];
						//additional fields
						
						//save model
						$AOC->save();
						//add to response array
						$inspection["AOCs"][] = $AOC;
					}
					
				}
				//Indications
				if($inspectionArray["Indications"] != null)
				{
					$inspection["Indications"] = [];
					//loop indications
					$IndicationCount = (count($inspectionArray["Indications"]));
					for ($i = 0; $i < $IndicationCount; $i++)
					{
						//new Indication model
						$indication = new AssetAddressIndication();
						//pass data to model
						$indication->attributes = $inspectionArray["Indications"][$i];
						//additional fields
						
						//save model
						$indication->save();
						//add to response array
						$inspection["Indications"][] = $indication;
					}
				}
				return $inspection;
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