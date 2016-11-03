<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/29/2016
 * Time: 12:45 PM
 */

namespace app\modules\v1\modules\pge\controllers;
use app\modules\v1\modules\pge\models\AssetAddressIndication;
use app\modules\v1\modules\pge\models\WebManagementMasterLeakLog;
use app\modules\v1\modules\pge\models\WebManagementLeaks;
use app\modules\v1\modules\pge\models\WebManagementEquipmentServices;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\TabletEquipment;
use app\modules\v1\modules\pge\models\InspectionsEquipment;
use app\modules\v1\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\data\Pagination;

class LeakLogController extends BaseActiveController {

    public $modelClass = 'app\modules\v1\modules\pge\models\WebManagementMasterLeakLog';

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
        //$behaviors['authenticator'] =
        //[
        //    'class' => TokenAuth::className(),
        //];
		$behaviors['verbs'] =
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'submit-leak' => ['put'],
                    'approve-leak' => ['put'],
					'get-details' => ['get'],
                    'get-detailsbymasterleaklogid' => ['get'],
					'get-mgnt' => ['get'],
                    'get-service-main-by-id'=>['get'],
                    'update-service-main'=>['put'],
                    'get-leak-log-by-id'=>['get'],
                    'update-leak-log'=>['put'],
                ],
            ];

		return $behaviors;
	}

    public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
    }

    public function actionSubmitLeak()
    {
        try
		{
            $headers = getallheaders();
            WebManagementMasterLeakLog::setClient($headers['X-Client']);

            $put = file_get_contents("php://input");
			$data = json_decode($put, true);
            $masterLeakUid = $data['masterleakUID'];
            $command =  WebManagementMasterLeakLog::getDb()->createCommand("EXEC dbo.spWebManagementMasterLeakLogSubmit @MasterLeakLogUID=:masterLeakUid, @SubmittedUID=:userId");
            $command->bindParam(":masterLeakUid", $masterLeakUid);
            $command->bindParam(":userId", $data['user']);
            $value = $command->queryAll();

            $response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $value;
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

    public function actionApproveLeak()
    {
        try
		{
            $headers = getallheaders();
            WebManagementMasterLeakLog::setClient($headers['X-Client']);

            $put = file_get_contents("php://input");
			$data = json_decode($put, true);
            $passed = 0;
            $failed = 0;
            $status = 'Unknown';
            foreach ($data['keylist'] as $indicationUID) {
                $command =  WebManagementMasterLeakLog::getDb()->createCommand("EXEC spWebManagementLeakLogApproval @AddressIndicationUID=:AddressIndicationUID, @ApproverUID=:ApproverUID");
                $command->bindParam(":AddressIndicationUID", $indicationUID);
                $command->bindParam(":ApproverUID", $data['user']);
                $value = $command->queryAll();
                if($value[0]['Succeeded'] == 1)
                {
                    $passed++;
                }
                else
                {
                    $failed++;
                }
                $status = $value[0]['StatusType'];
            }

            $result = [];
            $result['Passed'] = $passed;
            $result['Failed'] = $failed;
            $result['StatusType'] = $status;
            $response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $result;
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

    public function actionGetDetailsbymasterleaklogid($masterLeakLogUID)
	{
        try
		{
            $data = [];
            $headers = getallheaders();
            WebManagementMasterLeakLog::setClient($headers['X-Client']);
            $masterLeakLogRecords = WebManagementMasterLeakLog::find()
                ->where(['MasterLeakLogUID' => $masterLeakLogUID])
                ->all();
            return $this::actionGetDetails($masterLeakLogRecords[0]['Division'], $masterLeakLogRecords[0]['WorkCenter'], $masterLeakLogRecords[0]['Map/Plat'], $masterLeakLogRecords[0]['Surveyor'], $masterLeakLogRecords[0]['Date']);
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

                    $data["SAPExceptions"] = null;
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


    public function actionGetMgmt($division, $workCenter=null, $surveyor = null, $startDate = null, $endDate = null, $search = null, $status='', $page=1, $perPage=25)
	{
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementMasterLeakLog::setClient($headers['X-Client']);

            $counts = [];
            $counts['notApproved'] = 0;
            $counts['approvedOrNotSubmitted'] = 0;
            $counts['submittedOrPending'] = 0;
            $counts['exceptions'] = 0;
            $counts['completed'] = 0;

            if ($division && $workCenter) {
                $query = WebManagementMasterLeakLog::find();
                $query->where(['Division' => $division]);
                $query->andWhere(["WorkCenter" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
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
                if ($startDate !== null && $endDate !== null) {
                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $countersQuery = clone $query;
                if($status == 'Exceptions')
                {
                    $status = 'Rejected';
                }

                $status = trim($status);
                if ($status) {
                    $query->andWhere(["Status" => $status]);
                }
                $countQuery = clone $query;

                /* page index is 0 based */
                $page = max($page-1,0);
                $totalCount = $countQuery->count();
                $pages = new Pagination(['totalCount' => $totalCount]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPageSize($perPage);
                $pages->setPage($page,true);
                $offset = $pages->getOffset();//$perPage * ($page - 1);
                $limit = $pages->getLimit();
//                Yii::trace(PHP_EOL.PHP_EOL.'page '.$page.'   per page '.$perPage.'   offset '.$offset);
//                Yii::trace(PHP_EOL.'p getpage'.$pages->getPage().' p per page'.$pages->getPageSize().' p offset '.$pages->getOffset().'  p limit '.$pages->getLimit(). PHP_EOL);

                $query->orderBy(['Date' => SORT_ASC, 'Surveyor' => SORT_ASC, 'FLOC' => SORT_ASC, 'Hours' => SORT_ASC]);

                $leaks = $query->offset($offset)
                    ->limit($limit)
                    ->all();

                if ($division && $status && $workCenter) {
                    $countQueryNA = clone $countersQuery;
                    $countQueryA = clone $countersQuery;
                    $countQuerySP = clone $countersQuery;
                    $countQueryE = clone $countersQuery;
                    $countQueryC = clone $countersQuery;
                    //TODO rewrite to improve performance
                    $counts['notApproved'] = $countQueryNA
                        ->andWhere(['Status'=>'Not Approved'])
                        ->count();
                    $counts['approvedOrNotSubmitted'] = $countQueryA
                        ->andWhere(['Status'=>'Approved / Not Submitted'])
                        ->count();
                    $counts['submittedOrPending'] = $countQuerySP
                        ->andWhere(['Status'=>'Submitted / Pending'])
                        ->count();
                    $counts['exceptions'] = $countQueryE
                        ->andWhere(['Status'=>'Rejected'])
                        ->count();
                    $counts['completed'] = $countQueryC
                        ->andWhere(['Status'=>'Completed'])
                        ->count();
                }
            } else {
                $pages = new Pagination(['totalCount' => 0]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(0);
                $pages->setPageSize($perPage);
                $leaks =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $leaks;
            $data['pages'] = $pages;
            //            $data['totalCount']  = $totalCount;
            //            $data['offset'] = $pages->getOffset();
            //            $data['limit'] = $pages->getLimit();
            //            $command = $query->createCommand();
            //            $data['sql'] = $command->sql;
            //            $data['page'] = $page;
            //            $data['perPage'] = $perPage;

            $data['counts'] = $counts;

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
            Yii::trace($e->getMessage());
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

    /**
     * @param string $id InspectionServicesUID
     * @return \yii\console\Response|Response
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionGetServiceMainById($id) {
        try
        {
            $data = [];
            $inspectionServicesUID = $id;
            $headers = getallheaders();
            WebManagementEquipmentServices::setClient($headers['X-Client']);
            $smRecord = WebManagementEquipmentServices::find()
                ->where(['InspectionServicesUID' => $inspectionServicesUID])
                ->one();

            $data['result'] = $smRecord;

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

    /**
     * @param string $id InspectionServicesUID
     */
    public function actionUpdateServiceMain($id = null) {
        try
        {
            $headers = getallheaders();
            WebManagementMasterLeakLog::setClient($headers['X-Client']);

            $put = file_get_contents("php://input");
            $putData = json_decode($put, true);

//            Yii::trace(PHP_EOL.__CLASS__.' '.__METHOD__.' id = '.$id. ' putData = '.print_r($putData,true));
//            $sqlCommand = "EXEC spWebManagementServiceMainUpdate
//                            @InspectionServicesUID=:InspectionServicesUID,
//                            @putData=:putData";
            $sqlCommand = "Select '1' as Succeeded;";

            $command =  WebManagementMasterLeakLog::getDb()->createCommand($sqlCommand);
//            $command->bindParam(":InspectionServicesUID", $id);
//            $command->bindParam(":putData", $put);

            $result = $command->queryOne();

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $result;

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

    /**
     * @param string $id AssetAddressIndicationUID
     * @return \yii\console\Response|Response
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionGetLeakLogById($id) {
        try
        {
            $data = [];
            $assetAddressIndicationUID = $id;
            $headers = getallheaders();
            AssetAddressIndication::setClient($headers['X-Client']);
            $llRecord = AssetAddressIndication::find()
                ->where(['AssetAddressIndicationUID' => $assetAddressIndicationUID])
                ->andWhere(['ActiveFlag'=>'1'])
                ->one();

            $data['result'] = $llRecord;

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

    /**
     * @param string $id AssetAddressIndicationUID
     */
    public function actionUpdateLeakLog($id = null) {
        try
        {
            $headers = getallheaders();
            AssetAddressIndication::setClient($headers['X-Client']);

            $put = file_get_contents("php://input");
            $putData = json_decode($put, true);

//            Yii::trace(PHP_EOL.__CLASS__.' '.__METHOD__.' id = '.$id. ' putData = '.print_r($putData,true));
//            $sqlCommand = "EXEC spWebManagementLeakLogUpdate
//                            @AssetAddressIndicationUID=:AssetAddressIndicationUID,
//                            @putData=:putData";
            $sqlCommand = "Select '1' as Succeeded;";

            $command =  WebManagementMasterLeakLog::getDb()->createCommand($sqlCommand);
//            $command->bindParam(":AssetAddressIndicationUID", $id);
//            $command->bindParam(":putData", $put);

            $result = $command->queryOne();

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $result;

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