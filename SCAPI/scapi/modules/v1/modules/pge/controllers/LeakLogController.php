<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/29/2016
 * Time: 12:45 PM
 */

namespace app\modules\v1\modules\pge\controllers;

use app\modules\v1\modules\pge\models\WebManagementMasterLeakLog;
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

            $leak1a = [];
			$leak1a["Status"] = "Reviewed";
			$leak1a["Approved"] = "";
			$leak1a["HCA"] = "No";
			$leak1a["Leak #"] = "1111";
			$leak1a["SAP Leak #"] = "n/a";
			$leak1a["Above/Below Ground"] = "A";
			$leak1a["Reported By"] = "Foot Survey";
			$leak1a["Date Found"] = "05/10/2016";
			$leak1a["Time Found"] = "11:20";
			$leak1a["Address City"] = "Concord";
			$leak1a["Sorl"] = "A";
			$leak1a["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1a["Reading In %Gas"] = "1.5";
			$leak1a["Inst Type Found By"] = "I- Heath DPIR";
			$leak1a["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1a["Grd"] = "2+";
			$leak1a["Location Remarks"] = "test";
			$leak1a["Checkboxes"] = "true";
			$leak1a["Division"] = "Diablo";
			$leak1a["Map/Plat"] = "161-30-5-C";
			$leak1a["Surveyor"] = "johndoe";

            $leak1b = [];
			$leak1b["Status"] = "Reviewed";
			$leak1b["Approved"] = "";
			$leak1b["HCA"] = "No";
			$leak1b["Leak #"] = "1111";
			$leak1b["SAP Leak #"] = "n/a";
			$leak1b["Above/Below Ground"] = "A";
			$leak1b["Reported By"] = "Foot Survey";
			$leak1b["Date Found"] = "05/10/2016";
			$leak1b["Time Found"] = "11:20";
			$leak1b["Address City"] = "Concord";
			$leak1b["Sorl"] = "A";
			$leak1b["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1b["Reading In %Gas"] = "1.5";
			$leak1b["Inst Type Found By"] = "I- Heath DPIR";
			$leak1b["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1b["Grd"] = "2+";
			$leak1b["Location Remarks"] = "test";
			$leak1b["Checkboxes"] = "true";
			$leak1b["Division"] = "Diablo";
			$leak1b["Map/Plat"] = "161-30-5-C";
			$leak1b["Surveyor"] = "johndoe";

            $leak1c = [];
			$leak1c["Status"] = "Reviewed";
			$leak1c["Approved"] = "";
			$leak1c["HCA"] = "No";
			$leak1c["Leak #"] = "1111";
			$leak1c["SAP Leak #"] = "n/a";
			$leak1c["Above/Below Ground"] = "A";
			$leak1c["Reported By"] = "Foot Survey";
			$leak1c["Date Found"] = "05/10/2016";
			$leak1c["Time Found"] = "11:20";
			$leak1c["Address City"] = "Concord";
			$leak1c["Sorl"] = "A";
			$leak1c["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1c["Reading In %Gas"] = "1.5";
			$leak1c["Inst Type Found By"] = "I- Heath DPIR";
			$leak1c["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1c["Grd"] = "2+";
			$leak1c["Location Remarks"] = "test";
			$leak1c["Checkboxes"] = "true";
			$leak1c["Division"] = "Diablo";
			$leak1c["Map/Plat"] = "161-30-5-C";
			$leak1c["Surveyor"] = "johndoe";

            $leak1d = [];
			$leak1d["Status"] = "Reviewed";
			$leak1d["Approved"] = "";
			$leak1d["HCA"] = "No";
			$leak1d["Leak #"] = "1111";
			$leak1d["SAP Leak #"] = "n/a";
			$leak1d["Above/Below Ground"] = "A";
			$leak1d["Reported By"] = "Foot Survey";
			$leak1d["Date Found"] = "05/10/2016";
			$leak1d["Time Found"] = "11:20";
			$leak1d["Address City"] = "Concord";
			$leak1d["Sorl"] = "A";
			$leak1d["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1d["Reading In %Gas"] = "1.5";
			$leak1d["Inst Type Found By"] = "I- Heath DPIR";
			$leak1d["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1d["Grd"] = "2+";
			$leak1d["Location Remarks"] = "test";
			$leak1d["Checkboxes"] = "true";
			$leak1d["Division"] = "Diablo";
			$leak1d["Map/Plat"] = "161-30-5-C";
			$leak1d["Surveyor"] = "johndoe";

            $leak1e = [];
			$leak1e["Status"] = "Reviewed";
			$leak1e["Approved"] = "";
			$leak1e["HCA"] = "No";
			$leak1e["Leak #"] = "1111";
			$leak1e["SAP Leak #"] = "n/a";
			$leak1e["Above/Below Ground"] = "A";
			$leak1e["Reported By"] = "Foot Survey";
			$leak1e["Date Found"] = "05/10/2016";
			$leak1e["Time Found"] = "11:20";
			$leak1e["Address City"] = "Concord";
			$leak1e["Sorl"] = "A";
			$leak1e["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1e["Reading In %Gas"] = "1.5";
			$leak1e["Inst Type Found By"] = "I- Heath DPIR";
			$leak1e["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1e["Grd"] = "2+";
			$leak1e["Location Remarks"] = "test";
			$leak1e["Checkboxes"] = "true";
			$leak1e["Division"] = "Diablo";
			$leak1e["Map/Plat"] = "161-30-5-C";
			$leak1e["Surveyor"] = "johndoe";

            $leak1f = [];
			$leak1f["Status"] = "Reviewed";
			$leak1f["Approved"] = "";
			$leak1f["HCA"] = "No";
			$leak1f["Leak #"] = "1111";
			$leak1f["SAP Leak #"] = "n/a";
			$leak1f["Above/Below Ground"] = "A";
			$leak1f["Reported By"] = "Foot Survey";
			$leak1f["Date Found"] = "05/10/2016";
			$leak1f["Time Found"] = "11:20";
			$leak1f["Address City"] = "Concord";
			$leak1f["Sorl"] = "A";
			$leak1f["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1f["Reading In %Gas"] = "1.5";
			$leak1f["Inst Type Found By"] = "I- Heath DPIR";
			$leak1f["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1f["Grd"] = "2+";
			$leak1f["Location Remarks"] = "test";
			$leak1f["Checkboxes"] = "true";
			$leak1f["Division"] = "Diablo";
			$leak1f["Map/Plat"] = "161-30-5-C";
			$leak1f["Surveyor"] = "johndoe";

            $leak1g = [];
			$leak1g["Status"] = "Reviewed";
			$leak1g["Approved"] = "";
			$leak1g["HCA"] = "No";
			$leak1g["Leak #"] = "1111";
			$leak1g["SAP Leak #"] = "n/a";
			$leak1g["Above/Below Ground"] = "A";
			$leak1g["Reported By"] = "Foot Survey";
			$leak1g["Date Found"] = "05/10/2016";
			$leak1g["Time Found"] = "11:20";
			$leak1g["Address City"] = "Concord";
			$leak1g["Sorl"] = "A";
			$leak1g["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1g["Reading In %Gas"] = "1.5";
			$leak1g["Inst Type Found By"] = "I- Heath DPIR";
			$leak1g["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1g["Grd"] = "2+";
			$leak1g["Location Remarks"] = "test";
			$leak1g["Checkboxes"] = "true";
			$leak1g["Division"] = "Diablo";
			$leak1g["Map/Plat"] = "161-30-5-C";
			$leak1g["Surveyor"] = "johndoe";

            $leak1h = [];
			$leak1h["Status"] = "Reviewed";
			$leak1h["Approved"] = "";
			$leak1h["HCA"] = "No";
			$leak1h["Leak #"] = "1111";
			$leak1h["SAP Leak #"] = "n/a";
			$leak1h["Above/Below Ground"] = "A";
			$leak1h["Reported By"] = "Foot Survey";
			$leak1h["Date Found"] = "05/10/2016";
			$leak1h["Time Found"] = "11:20";
			$leak1h["Address City"] = "Concord";
			$leak1h["Sorl"] = "A";
			$leak1h["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
			$leak1h["Reading In %Gas"] = "1.5";
			$leak1h["Inst Type Found By"] = "I- Heath DPIR";
			$leak1h["Inst Type Grade By"] = "I- Heath DPIR";
			$leak1h["Grd"] = "2+";
			$leak1h["Location Remarks"] = "test";
			$leak1h["Checkboxes"] = "true";
			$leak1h["Division"] = "Diablo";
			$leak1h["Map/Plat"] = "161-30-5-C";
			$leak1h["Surveyor"] = "johndoe";

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

            $service2a = [];
			$service2a["Date"] = "05/10/2016";
			$service2a["Operator ID"] = "johndoe";
			$service2a["Inst Type"] = "I-Heath DPIR";
			$service2a["Inst Serial #"] = "7786829100";
			$service2a["Equip Mode"] = "G";
			$service2a["Feet of Main"] = "0";
			$service2a["# of Services"] = "0";
			$service2a["Hours"] = "1";
			$service2a["Division"] = "Diablo";
			$service2a["Map/Plat"] = "161-30-5-C";

            $service2b = [];
			$service2b["Date"] = "05/10/2016";
			$service2b["Operator ID"] = "johndoe";
			$service2b["Inst Type"] = "I-Heath DPIR";
			$service2b["Inst Serial #"] = "7786829100";
			$service2b["Equip Mode"] = "G";
			$service2b["Feet of Main"] = "0";
			$service2b["# of Services"] = "0";
			$service2b["Hours"] = "1";
			$service2b["Division"] = "Diablo";
			$service2b["Map/Plat"] = "161-30-5-C";

            $service2c = [];
			$service2c["Date"] = "05/10/2016";
			$service2c["Operator ID"] = "johndoe";
			$service2c["Inst Type"] = "I-Heath DPIR";
			$service2c["Inst Serial #"] = "7786829100";
			$service2c["Equip Mode"] = "G";
			$service2c["Feet of Main"] = "0";
			$service2c["# of Services"] = "0";
			$service2c["Hours"] = "1";
			$service2c["Division"] = "Diablo";
			$service2c["Map/Plat"] = "161-30-5-C";

            $service2d = [];
			$service2d["Date"] = "05/10/2016";
			$service2d["Operator ID"] = "johndoe";
			$service2d["Inst Type"] = "I-Heath DPIR";
			$service2d["Inst Serial #"] = "7786829100";
			$service2d["Equip Mode"] = "G";
			$service2d["Feet of Main"] = "0";
			$service2d["# of Services"] = "0";
			$service2d["Hours"] = "1";
			$service2d["Division"] = "Diablo";
			$service2d["Map/Plat"] = "161-30-5-C";

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
            $leaks[] = $leak1a;
            $leaks[] = $leak1b;
            $leaks[] = $leak1c;
            $leaks[] = $leak1d;
            $leaks[] = $leak1e;
            $leaks[] = $leak1f;
            $leaks[] = $leak1g;
            $leaks[] = $leak1h;

			$leaks[] = $leak2;
			$leaks[] = $leak3;
			$leaks[] = $leak4;
			$leakCount = count($leaks);

			$services[] = $service1;
			$services[] = $service2;
            $services[] = $service2a;
            $services[] = $service2b;
            $services[] = $service2c;
            $services[] = $service2d;

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


    public function actionGetMgmt($workCenter, $surveyor = null, $startDate, $endDate, $search = null)
	{
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementMasterLeakLog::setClient($headers['X-Client']);

            $values = WebManagementMasterLeakLog::find()
                ->where(['WorkCenter' => $workCenter]);

            if ($surveyor)
                $values = $values->where(["Surveyor" => $surveyor]);

            if ($search) {
                $values = $values->where([
                    'or',
                    ['like', 'Leaks', $search],
                    ['like', 'Division', $search],
                    ['like', 'Approved', $search],
                    ['like', 'HCA', $search],
                    ['like', 'Date', $search],
                    ['like', 'Surveyor', $search],
                    ['like', 'WorkCenter', $search],
                    ['like', 'FLOC', $search],
                    ['like', 'SurveyFreq', $search],
                    ['like', 'FeetOfMain', $search],
                    ['like', 'NumofServices', $search],
                    ['like', 'Hours', $search]
                ]);
            }

            $leaks = $values->all();

			$data = [];
			$data['Not Approved'] = [];
			$data['Approved / Not Submitted'] = [];
			$data['Submitted / Pending'] = [];
			$data['Exceptions'] = [];
			$data['Completed'] = [];

			// filter leaks
            foreach ($leaks as $leak) {
                if(BaseActiveController::inDateRange($leak["Date"], $startDate, $endDate))
                {
                    $data[$leak["Status"]][] = $leak;
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

	public function actionGetTransferFloc() {
        $data = [];

        $data['Lan ID'] = 'PGE1';
        $data['Date'] = "8/29/2016 18:16";
        $data['Map-Plat'] = '0042-D13 (3 Year)';

        $data['Approval Lan ID'] = 'SUP1';
        $data['Approval Date'] = '08/31/2016 09:43';

        $currentData = [];
        $currentData['Work Center'] = 'San Fransisco';
        $currentData['FLOC'] = 'GD.PHYS.SNFA.0042.0D13';
        $newData = [];
        $newData['Work Center'] =
            [
                'San Francisco' => 'San Francisco',
                'New York City' => 'New York City'
            ];
        $newData['FLOC'] =
            [
                'GD.PHYS.SNFC.0001.0F12' => 'GD.PHYS.SNFC.0001.0F12',
                'GD.PHYS.SNFC.0002.0F13' => 'GD.PHYS.SNFC.0002.0F13'
            ];
        $records = [];
        $toBeTransfered = [];
        $toBeTransfered['Equipment'] = 3;
        $toBeTransfered['Leaks'] = 4;
        $records['toBeTransfered'] = $toBeTransfered;
        $completed = [];
        $completed['Equipment'] = 0;
        $completed['Leaks'] = 0;
        $records['Completed'] = $completed;

        $data['approved'] = true;
        $data['currentData'] = $currentData;
        $data['newData'] = $newData;
        $data['records'] = $records;


        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }
}