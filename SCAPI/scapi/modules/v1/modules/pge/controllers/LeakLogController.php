<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/29/2016
 * Time: 12:45 PM
 */

namespace app\modules\v1\modules\pge\controllers;
use app\modules\v1\modules\pge\models\WebManagementMasterLeakLog;
use app\modules\v1\modules\pge\models\WebManagementLeaks;
use app\modules\v1\modules\pge\models\WebManagementEquipmentServices;
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

    public function actionGetDetails($division, $workCenter, $mapPlat, $surveyor, $date)
	{
        try
		{
            $data = [];

            if($division != null && $workCenter != null && $mapPlat != null && $surveyor != null && $date != null)
            {
                $headers = getallheaders();
                WebManagementMasterLeakLog::setClient($headers['X-Client']);
                WebManagementLeaks::setClient($headers['X-Client']);
                WebManagementEquipmentServices::setClient($headers['X-Client']);

                $masterLeakLogRecords = WebManagementMasterLeakLog::find()
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
                    ->andWhere(['Map/Plat' => $mapPlat])
                    ->andWhere(['Surveyor' => $surveyor])
                    ->andWhere(['Date' => $date])
                    ->all();

                if(count($masterLeakLogRecords) == 1)
                {
                    $data["MasterLeakLog"] = $masterLeakLogRecords[0];
                    $uid = $masterLeakLogRecords[0]['MasterLeakLogUID'];
                    $leakValues = WebManagementLeaks::find()
                        ->where(['MasterLeakLogUID' => $uid])
                        ->all();

                    $serviceValues = WebManagementEquipmentServices::find()
                        ->where(['MasterLeakLogUID' => $uid])
                        ->all();

                    foreach ($leakValues as $leak) {
                        $data["Leaks"][] = $leak;
                    }

                    foreach ($serviceValues as $service) {
                        $data["Services"][] = $service;
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