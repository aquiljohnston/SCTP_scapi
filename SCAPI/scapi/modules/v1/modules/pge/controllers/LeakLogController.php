<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/29/2016
 * Time: 12:45 PM
 */

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class LeakLogController extends Controller {

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'get-details' => ['get'],
					'get-mgnt' => ['get'],
                ],  
            ];
		return $behaviors;	
	}

    public function actionGetDetails($division, $mapPlat, $surveyor, $date) 
	{
        try
		{
			$leak1 = [];
			$leak1["Status"] = "Reviewed";
			$leak1["Approved"] = "";
			$leak1["HCA"] = "No";
			$leak1["Leak #"] = "1111";
			$leak1["SAP Leak #"] = "n/a";
			$leak1["Above/Below Ground"] = "A";
			$leak1["Reported By"] = "Foot Survey";
			$leak1["Date Found"] = "05/10/2016";
			$leak1["Time Found"] = "11:20";
			$leak1["Address City"] = "Concord";
			$leak1["Sorl"] = "A";
			$leak1["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1["Reading In %Gas"] = "1.5";
			$leak1["Inst Type Found By"] = "I- Heath DPIR";
			$leak1["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1["Grd"] = "2+";
			$leak1["Location Remarks"] = "test";
			$leak1["Checkboxes"] = "true";
			$leak1["Division"] = "Diablo";
			$leak1["Map/Plat"] = "161-30-5-C";
			$leak1["Surveyor"] = "johndoe";
			
			$leak2 = [];
			$leak2["Status"] = "Reviewed";
			$leak2["Approved"] = "";
			$leak2["HCA"] = "No";
			$leak2["Leak #"] = "1112";
			$leak2["SAP Leak #"] = "n/a";
			$leak2["Above/Below Ground"] = "A";
			$leak2["Reported By"] = "Call In";
			$leak2["Date Found"] = "05/12/2016";
			$leak2["Time Found"] = "1:20";
			$leak2["Address City"] = "Concord";
			$leak2["Sorl"] = "A";
			$leak2["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak2["Reading In %Gas"] = "1.5";
			$leak2["Inst Type Found By"] = "I- Heath DPIR";
			$leak2["Inst Type Grade By"] = "I- Heath DPIR";
			$leak2["Grd"] = "2+";
			$leak2["Location Remarks"] = "test";
			$leak2["Checkboxes"] = "true";
			$leak2["Division"] = "Azmodan";
			$leak2["Map/Plat"] = "141-31-3-C";
			$leak2["Surveyor"] = "bob1";
			
			$leak3 = [];
			$leak3["Status"] = "Reviewed";
			$leak3["Approved"] = "";
			$leak3["HCA"] = "No";
			$leak3["Leak #"] = "1113";
			$leak3["SAP Leak #"] = "n/a";
			$leak3["Above/Below Ground"] = "B";
			$leak3["Reported By"] = "Foot Survey";
			$leak3["Date Found"] = "05/13/2016";
			$leak3["Time Found"] = "1:20";
			$leak3["Address City"] = "Concord";
			$leak3["Sorl"] = "A";
			$leak3["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak3["Reading In %Gas"] = "1.5";
			$leak3["Inst Type Found By"] = "I- Heath DPIR";
			$leak3["Inst Type Grade By"] = "I- Heath DPIR";
			$leak3["Grd"] = "2+";
			$leak3["Location Remarks"] = "test";
			$leak3["Checkboxes"] = "true";
			$leak3["Division"] = "Malthael";
			$leak3["Map/Plat"] = "120-31-6-F";
			$leak3["Surveyor"] = "bill2";
			
			$leak4 = [];
			$leak4["Status"] = "Reviewed";
			$leak4["Approved"] = "";
			$leak4["HCA"] = "No";
			$leak4["Leak #"] = "1114";
			$leak4["SAP Leak #"] = "n/a";
			$leak4["Above/Below Ground"] = "B";
			$leak4["Reported By"] = "Foot Survey";
			$leak4["Date Found"] = "05/14/2016";
			$leak4["Time Found"] = "1:20";
			$leak4["Address City"] = "Concord";
			$leak4["Sorl"] = "A";
			$leak4["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak4["Reading In %Gas"] = "1.5";
			$leak4["Inst Type Found By"] = "I- Heath DPIR";
			$leak4["Inst Type Grade By"] = "I- Heath DPIR";
			$leak4["Grd"] = "2+";
			$leak4["Location Remarks"] = "test";
			$leak4["Checkboxes"] = "true";
			$leak4["Division"] = "Malthael";
			$leak4["Map/Plat"] = "110-11-3-A";
			$leak4["Surveyor"] = "fred3";
			
			$service1 = [];
			$service1["Date"] = "05/11/2016";
			$service1["Operator ID"] = "janedoe";
			$service1["Inst Type"] = "I-Heath DPIR";
			$service1["Inst Serial #"] = "6717771107";
			$service1["Equip Mode"] = "F";
			$service1["Feet of Main"] = "2020";
			$service1["# of Services"] = "120";
			$service1["Hours"] = "1";
			$service1["Division"] = "Diablo";
			$service1["Map/Plat"] = "161-30-3-C";
			
			$service2 = [];
			$service2["Date"] = "05/10/2016";
			$service2["Operator ID"] = "johndoe";
			$service2["Inst Type"] = "I-Heath DPIR";
			$service2["Inst Serial #"] = "7786829100";
			$service2["Equip Mode"] = "G";
			$service2["Feet of Main"] = "0";
			$service2["# of Services"] = "0";
			$service2["Hours"] = "1";
			$service2["Division"] = "Diablo";
			$service2["Map/Plat"] = "161-30-5-C";
			
			$service3 = [];
			$service3["Date"] = "05/12/2016";
			$service3["Operator ID"] = "bob1";
			$service3["Inst Type"] = "I-Heath DPIR";
			$service3["Inst Serial #"] = "6717771107";
			$service3["Equip Mode"] = "F";
			$service3["Feet of Main"] = "20";
			$service3["# of Services"] = "10";
			$service3["Hours"] = "1";
			$service3["Division"] = "Azmodan";
			$service3["Map/Plat"] = "141-31-3-C";
			
			$service4 = [];
			$service4["Date"] = "05/13/2016";
			$service4["Operator ID"] = "bill2";
			$service4["Inst Type"] = "I-Heath DPIR";
			$service4["Inst Serial #"] = "6717771107";
			$service4["Equip Mode"] = "F";
			$service4["Feet of Main"] = "15";
			$service4["# of Services"] = "1";
			$service4["Hours"] = "8";
			$service4["Division"] = "Malthael";
			$service4["Map/Plat"] = "120-31-6-F";

			$leaks = [];
			$services = [];
			$data = [];
			
			$leaks[] = $leak1;
			$leaks[] = $leak2;
			$leaks[] = $leak3;
			$leaks[] = $leak4;
			$leakCount = count($leaks);
			
			$services[] = $service1;
			$services[] = $service2;
			$services[] = $service3;
			$services[] = $service4;
			$serviceCount = count($services);
			
			//filter leaks
			for($i = 0 ; $i < $leakCount ; $i++)
			{
				if($leaks[$i]["Division"] == $division)
				{
					if($leaks[$i]["Map/Plat"] == $mapPlat)
					{
						if($leaks[$i]["Surveyor"] == $surveyor)
						{
							if($leaks[$i]["Date Found"] == $date)
							{
								$data["Leaks"][] = $leaks[$i];
							}
						}
					}
				}
			}
			
			//filter services
			for($i = 0 ; $i < $serviceCount ; $i++)
			{
				if($services[$i]["Division"] == $division)
				{
					if($services[$i]["Map/Plat"] == $mapPlat)
					{
						if($services[$i]["Operator ID"] == $surveyor)
						{
							if($services[$i]["Date"] == $date)
							{
								$data["Services"][] = $services[$i];
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


    public function actionGetMgmt($workCenter, $surveyor = null, $startDate, $endDate)
	{
		try{
			$leak1 = [];
			$leak1["Leak"] = "1";
			$leak1["Approved"] = "0";
			$leak1["HCA"] = "0";
			$leak1["Date"] = "05/10/2016";
			$leak1["Employee"] = "Doe, John (johndoe)";
			$leak1["Work Center"] = "Izual";
			$leak1["Map"] = "161-30-5-C";
			$leak1["Plat"] = "161-30-5-C";
			$leak1["Survey Type"] = "5 Year";
			$leak1["Pipeline Type"] = "GD";
			$leak1["#of Services"] = "0";
			$leak1["Feet Of Main"] = "0";
			$leak1["Hours"] = "1";
			$leak1["Exception"] = "";
			$leak1["Status"] = "Not Approved";
			$leak1["Tab"] = "Not Approved";
			
			$leak2 = [];
			$leak2["Leak"] = "1";
			$leak2["Approved"] = "1";
			$leak2["HCA"] = "0";
			$leak2["Date"] = "05/11/2016";
			$leak2["Employee"] = "Doe, Jane (janedoe)";
			$leak2["Work Center"] = "Izual";
			$leak2["Map"] = "161-30-5-C";
			$leak2["Plat"] = "161-30-5-C";
			$leak2["Survey Type"] = "1 Year";
			$leak2["Pipeline Type"] = "GD";
			$leak2["#of Services"] = "120";
			$leak2["Feet Of Main"] = "2020";
			$leak2["Hours"] = "1";
			$leak2["Exception"] = "";
			$leak2["Status"] = "Not Submitted";
			$leak2["Tab"] = "Approved / Not Submitted";

			$leak3 = [];
			$leak3["Leak"] = "1";
			$leak3["Approved"] = "1";
			$leak3["HCA"] = "0";
			$leak3["Date"] = "05/12/2016";
			$leak3["Employee"] = "Smith, Bob (bob1)";
			$leak3["Work Center"] = "Cydaea";
			$leak3["Map"] = "141-31-3-C";
			$leak3["Plat"] = "141-31-3-C";
			$leak3["Survey Type"] = "Semi-Annual";
			$leak3["Pipeline Type"] = "GD";
			$leak3["#of Services"] = "20";
			$leak3["Feet Of Main"] = "500";
			$leak3["Hours"] = "3";
			$leak3["Exception"] = "";
			$leak3["Status"] = "Pending";
			$leak3['Tab'] = 'Submitted / Pending';

			$leak4 = [];
			$leak4["Leak"] = "1";
			$leak4["Approved"] = "1";
			$leak4["HCA"] = "0";
			$leak4["Date"] = "05/12/2016";
			$leak4["Employee"] = "Randalt, Bill (bill2)";
			$leak4["Work Center"] = "Urzael";
			$leak4["Map"] = "141-31-3-C";
			$leak4["Plat"] = "141-31-3-C";
			$leak4["Survey Type"] = "Special";
			$leak4["Pipeline Type"] = "GD";
			$leak4["#of Services"] = "1";
			$leak4["Feet Of Main"] = "15";
			$leak4["Hours"] = "8";
			$leak4["Exception"] = "1";
			$leak4["Status"] = "Rejected";
			$leak4["Tab"] = "Exceptions";

			$leak5 = [];
			$leak5["Leak"] = "1";
			$leak5["Approved"] = "1";
			$leak5["HCA"] = "0";
			$leak5["Date"] = "05/12/2016";
			$leak5["Employee"] = "Milstone, Fred (fred3)";
			$leak5["Work Center"] = "Urzael";
			$leak5["Map"] = "141-31-3-C";
			$leak5["Plat"] = "141-31-3-C";
			$leak5["Survey Type"] = "Immediate Response";
			$leak5["Pipeline Type"] = "GD";
			$leak5["#of Services"] = "0";
			$leak5["Feet Of Main"] = "0";
			$leak5["Hours"] = "0";
			$leak5["Exception"] = "";
			$leak5["Status"] = "Completed";
			$leak5["Tab"] = "Completed";

			$leaks[] = $leak1;
			$leaks[] = $leak2;
			$leaks[] = $leak3;
			$leaks[] = $leak4;
			$leaks[] = $leak5;
			$leakCount = count($leaks);
			
			$data = [];
			$data['Not Approved'] = [];
			$data['Approved / Not Submitted'] = [];
			$data['Submitted / Pending'] = [];
			$data['Exceptions'] = [];
			$data['Completed'] = [];
			
			//filter leaks
			for($i = 0 ; $i < $leakCount ; $i++)
			{
				$username = explode('(', $leaks[$i]['Employee']);
				$username = trim($username[1], ')');
				if($leaks[$i]["Work Center"] == $workCenter)
				{
					if($surveyor == null || $username == $surveyor)
					{
						if(BaseActiveController::inDateRange($leaks[$i]["Date"], $startDate, $endDate))
						{
							$data[$leaks[$i]['Tab']][] = $leaks[$i];
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
}