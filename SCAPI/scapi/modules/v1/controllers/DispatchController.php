<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

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
					'get-unassigned' => ['get'],
					'get-assigned' => ['get'],
					'get-surveyors' => ['get']
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGetUnassigned($division = null, $workCenter = null, $mapPlat = null, $surveyType = null, $complianceMonth = null, $filter = null)
	{
		try
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
			$asset5["Survey Type"] = "Special";
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
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGetAssigned($division = null, $workCenter = null, $mapPlat = null, $status = null, $dispatchMethod = null, $complianceMonth = null, $filter = null)
	{
		try
		{
			$asset1 = [];
			$asset1["Division"] = "Diablo";
			$asset1["Work Center"] = "Izual";
			$asset1["Survey Type"] = "3 YR";
			$asset1["Map/Plat"] = "161-30-5-C";
			$asset1["Notification ID"] = "12345678";
			$asset1["Surveyor"] = "Doe, John";
			$asset1["Employee Type"] = "Employee";
			$asset1["Compliance Date"] = "08/10/2016";
			$asset1["Status"] = "Accepted";
			$asset1["Dispatch Method"] = "Dispatched";
			
			$asset2 = [];
			$asset2["Division"] = "Malthael";
			$asset2["Work Center"] = "Urzael";
			$asset2["Survey Type"] = "1 YR";
			$asset2["Map/Plat"] = "120-31-6-F";
			$asset2["Notification ID"] = "13572468";
			$asset2["Surveyor"] = "Doe, Jane";
			$asset2["Employee Type"] = "Employee";
			$asset2["Compliance Date"] = "08/11/2016";
			$asset2["Status"] = "Dispatched";
			$asset2["Dispatch Method"] = "Self Dispatched";
			
			$asset3 = [];
			$asset3["Division"] = "Azmodan";
			$asset3["Work Center"] = "Cydaea";
			$asset3["Survey Type"] = "5 YR";
			$asset3["Map/Plat"] = "141-31-3-C";
			$asset3["Notification ID"] = "24681357";
			$asset3["Surveyor"] = "Smith, Bob";
			$asset3["Employee Type"] = "Employee";
			$asset3["Compliance Date"] = "08/09/2016";
			$asset3["Status"] = "In Progress";
			$asset3["Dispatch Method"] = "Ad Hoc";
			
			$asset4 = [];
			$asset4["Division"] = "Belial";
			$asset4["Work Center"] = "Zoltun Kulle";
			$asset4["Survey Type"] = "Semi Annual";
			$asset4["Map/Plat"] = "133-34-4-C";
			$asset4["Notification ID"] = "12563478";
			$asset4["Surveyor"] = "Milstone, Fred";
			$asset4["Employee Type"] = "Employee";
			$asset4["Compliance Date"] = "08/12/2016";
			$asset4["Status"] = "Accepted";
			$asset4["Dispatch Method"] = "Dispatched";
			
			$assets = [];
			$assets[] = $asset1;
			$assets[] = $asset2;
			$assets[] = $asset3;
			$assets[] = $asset4;
			$assetCount = count($assets);
			
			$data = [];
			
			//filter assets
			for($i = 0; $i < $assetCount; $i++)
			{
				if($filter == null || stripos($assets[$i]["Division"], $filter) !== false || stripos($assets[$i]["Work Center"], $filter) !== false
				|| stripos($assets[$i]["Survey Type"], $filter) !== false || stripos($assets[$i]["Map/Plat"], $filter) !== false
				|| stripos($assets[$i]["Notification ID"], $filter) !== false || stripos($assets[$i]["Surveyor"], $filter) !== false
				|| stripos($assets[$i]["Employee Type"], $filter) !== false || stripos($assets[$i]["Compliance Date"], $filter) !== false
				|| stripos($assets[$i]["Status"], $filter) !== false || stripos($assets[$i]["Dispatch Method"], $filter) !== false)
				{
					if($division == null || $assets[$i]["Division"] == $division)
					{
						if($workCenter == null || $assets[$i]["Work Center"] == $workCenter)
						{
							if($mapPlat == null || $assets[$i]["Map/Plat"] == $mapPlat)
							{
								if($status == null || $assets[$i]["Status"] == $status)
								{
									if($dispatchMethod == null || $assets[$i]["Dispatch Method"] == $dispatchMethod)
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
			}
			
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
	
	public function actionGetSurveyors(/*$surveyor = null,*/$filter = null)
	{
		try
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