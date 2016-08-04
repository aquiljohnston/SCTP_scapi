<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;

class DispatchController extends Controller 
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
					'get-dispatch' => ['get'],
					'get-surveyors' => ['get']
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGetUnassigned($division = null, $workCenter = null, $mapPlat = null, $surveyType = null, $complianceMonth = null, $filter = null)
	{
		$asset1 = [];
		$asset1["Division"] = "Diablo";
		$asset1["Work Center"] = "Izual";
		$asset1["Survey Type"] = "3 YR";
		$asset1["Map/Plat"] = "161-30-5-C";
		$asset1["Notification ID"] = "12345678";
		$asset1["Compliance Date"] = "08/15/2016";
		$asset1["SAP Released"] = "01/01/2016";
		$asset1["Assigned"] = "0";
		
		$asset2 = [];
		$asset2["Division"] = "Malthael";
		$asset2["Work Center"] = "Urzael";
		$asset2["Survey Type"] = "5 YR";
		$asset2["Map/Plat"] = "120-31-6-F";
		$asset2["Notification ID"] = "13572468";
		$asset2["Compliance Date"] = "08/20/2016";
		$asset2["SAP Released"] = "01/01/2016";
		$asset2["Assigned"] = "0";
		
		$asset3 = [];
		$asset3["Division"] = "Belial";
		$asset3["Work Center"] = "Zoltun Kulle";
		$asset3["Survey Type"] = "1 YR";
		$asset3["Map/Plat"] = "133-34-4-C";
		$asset3["Notification ID"] = "24681357";
		$asset3["Compliance Date"] = "09/05/2016";
		$asset3["SAP Released"] = "01/01/2016";
		$asset3["Assigned"] = "0";
		
		$asset4 = [];
		$asset4["Division"] = "Azmodan";
		$asset4["Work Center"] = "Cydaea";
		$asset4["Survey Type"] = "3 YR";
		$asset4["Map/Plat"] = "141-31-3-C";
		$asset4["Notification ID"] = "12563478";
		$asset4["Compliance Date"] = "09/12/2016";
		$asset4["SAP Released"] = "01/01/2016";
		$asset4["Assigned"] = "0";
		
		$asset5 = [];
		$asset5["Division"] = "Diablo";
		$asset5["Work Center"] = "Izual";
		$asset5["Survey Type"] = "3 YR";
		$asset5["Map/Plat"] = "161-30-3-C";
		$asset5["Notification ID"] = "23146758";
		$asset5["Compliance Date"] = "09/28/2016";
		$asset5["SAP Released"] = "01/01/2016";
		$asset5["Assigned"] = "0";
		
		$assets = [];
		$assets[] = $asset1;
		$assets[] = $asset2;
		$assets[] = $asset3;
		$assets[] = $asset4;
		$assets[] = $asset5;
		$assetCount = count($assets);
		
		$data = [];
		
		//filter assets
		for($i = 0; $i < $assetCount; $i++)
		{
			if($filter == null || stripos($assets[$i]["Division"], $filter) !== false || stripos($assets[$i]["Work Center"], $filter) !== false
			|| stripos($assets[$i]["Survey Type"], $filter) !== false || stripos($assets[$i]["Map/Plat"], $filter) !== false
			|| stripos($assets[$i]["Notification ID"], $filter) !== false || stripos($assets[$i]["Compliance Date"], $filter) !== false
			|| stripos($assets[$i]["SAP Released"], $filter) !== false || stripos($assets[$i]["Assigned"], $filter) !== false)
			{
				if($division == null || $assets[$i]["Division"] == $division)
				{
					if($workCenter == null || $assets[$i]["Work Center"] == $workCenter)
					{
						if($mapPlat == null || $assets[$i]["Map/Plat"] == $mapPlat)
						{
							if($surveyType == null || $assets[$i]["Survey Type"] == $surveyType)
							{
								$dateArray = explode("/", $assets[$i]["Compliance Date"]);
								$month = $dateArray[0];
								//compliance month
								if($complianceMonth == null || $month == $complianceMonth)
								{
									$data[] = $assets[$i];
								}
							}
						}
					}
				}
			}
		}
		
		//send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
	}
	
	public function actionGetAssigned()
	{
		$data = [];
		
		//send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
	}
	
	public function actionGetSurveyors(/*$surveyor = null,*/$filter = null)
	{
		$surveyor1 =[];
		$surveyor1["Name"] = "Doe, John";
		$surveyor1["LANID"] = "J0D1";
		
		$surveyor2 =[];
		$surveyor2["Name"] = "Doe, Jane";
		$surveyor2["LANID"] = "J0D2";
		
		$surveyor3 =[];
		$surveyor3["Name"] = "Smith, Bob";
		$surveyor3["LANID"] = "B2S4";
		
		$surveyor4 =[];
		$surveyor4["Name"] = "Milstone, Fred";
		$surveyor4["LANID"] = "F3M4";
		
		$surveyors = [];
		$surveyors[] = $surveyor1;
		$surveyors[] = $surveyor2;
		$surveyors[] = $surveyor3;
		$surveyors[] = $surveyor4;
		$surveyorCount = count($surveyors);
		
		$data = [];
		
		//filter surveyors
		for($i = 0; $i < $surveyorCount; $i++)
		{
			if($filter == null || stripos($surveyors[$i]["Name"], $filter) !== false || stripos($surveyors[$i]["LANID"], $filter) !== false)
			{
				$data[] = $surveyors[$i];
			}
		}
		
		//send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
	}
}