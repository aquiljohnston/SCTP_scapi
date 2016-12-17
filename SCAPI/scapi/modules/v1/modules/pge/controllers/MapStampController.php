<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 8/3/2016
 * Time: 2:40 PM
 */

namespace app\modules\v1\modules\pge\controllers;

use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\WebManagementEquipmentServicesPic;
use Yii;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v1\modules\pge\models\WebManagementMapStampDetail;
use app\modules\v1\modules\pge\models\WebManagementMapStampManagement;
use yii\data\Pagination;


class MapStampController extends BaseActiveController {
    public $modelClass = 'app\modules\v1\modules\pge\models\WebManagementMapStampDetail';

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
                    'get-mgmt' => ['get'],
                    'get-detail' => ['get'],
                    'submit-stamp' => ['put'],
                    'get-equipment-picaro-by-id' => ['get'],
                    'update-equipment-picaro' => ['put']
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

    public function actionSubmitStamp()
    {
        try
		{
            $headers = getallheaders();
            WebManagementMapStampDetail::setClient($headers['X-Client']);

            $put = file_get_contents("php://input");
			$data = json_decode($put, true);
            $inspectionRequestUID = $data['inspectionRequestUID'];
            $command =  WebManagementMapStampDetail::getDb()->createCommand("EXEC dbo.spWebManagementMapStampSubmit @InspectionRequestUID=:inspectionRequestUID, @SubmittedUID=:userId");
            $command->bindParam(":inspectionRequestUID", $inspectionRequestUID);
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

    public function actionGetMgmt($division, $workCenter=null, $startDate = null, $endDate = null, $search = null, $status='', $page=1, $perPage=25)
    {
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementMapStampManagement::setClient($headers['X-Client']);

            $counts = [];
            $counts['inProgress'] = 0;
            $counts['submittedPending'] = 0;
            $counts['returned'] = 0;
            $counts['completed'] = 0;

            if ($division && $workCenter) {
                $query = WebManagementMapStampManagement::find();
                $query->where(['Division' => $division]);
                $query->andWhere(['WorkCenter' => $workCenter]);

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', 'WorkCenter', $search],
                        ['like', 'FLOC', $search],
                        ['like', 'SurveyType', $search],
                        ['like', 'InspectionType', $search],
                        ['like', 'ComplianceDate', $search],
                        ['like', 'TotalNoOfDays', $search],
//                        ['like', 'Total # Of Days', $search],
                        ['like', 'TotalNoOfLeaks', $search],
                        ['like', 'TotalFeetOfMain', $search],
                        ['like', 'TotalServices', $search],
                    ]);
                }

                // selects all entries for which the interval defined by MapStamp DetailStartDate and MapStamp DetailEndDate
                // intersects the interval defined by the date filter
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' take into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere([
                        'or',
                        ['between', 'DetailStartDate', $startDate, $endDate],
                        ['between', 'DetailEndDate', $startDate, $endDate]
                    ]);
                }

                $countersQuery = clone $query;
                $status = trim($status);
                if ($status) {
                    $query->andWhere(['MapStampStatus' => $status]);
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

                $query->orderBy(['ComplianceDate' => SORT_ASC, 'FLOC' => SORT_ASC]);

                $entries = $query->offset($offset)
                    ->limit($limit)
                    ->all();

                if ($division && $status && $workCenter) {
                    $countQueryInProgress = clone $countersQuery;
                    $countQueryPending = clone $countersQuery;
                    $countQueryReturned = clone $countersQuery;
                    $countQueryCompleted = clone $countersQuery;
                    //TODO rewrite to improve performance
                    $counts['inProgress'] = $countQueryInProgress
                        ->andWhere(['MapStampStatus'=>'In Progress'])
                        ->count();
                    $counts['submittedPending'] = $countQueryPending
                        ->andWhere(['MapStampStatus'=>'Submit/Pending'])
                        ->count();
                    $counts['returned'] = $countQueryReturned
                        ->andWhere(['MapStampStatus'=>'Returned'])
                        ->count();
                    $counts['completed'] = $countQueryCompleted
                        ->andWhere(['MapStampStatus'=>'Completed'])
                        ->count();
                }
            } else {
                $pages = new Pagination(['totalCount' => 0]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(0);
                $pages->setPageSize($perPage);
                $entries =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $entries;
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

    public function actionGetDetail($id) {
        if($id === "") {
            throw new BadRequestHttpException("Empty ID argument");
        }

        $headers = getallheaders();
        WebManagementMapStampManagement::setClient($headers['X-Client']);
        WebManagementMapStampDetail::setClient($headers['X-Client']);

        $generalInfo = [];
        $entriesDetails = [];
        $data = [];

        if ($id) {
            $queryMgmt = WebManagementMapStampManagement::find()->where(['InspectionRequestUID'=>$id]);
            $queryDetails = WebManagementMapStampDetail::find()->where(['IRUID'=>$id]);

            $generalInfo = $queryMgmt->one();
            $queryDetails->orderBy(['SortOrder'=>SORT_ASC,'Seq'=>SORT_ASC,'SurveyArea'=>SORT_ASC,'DateSurveyed' => SORT_ASC, 'InstSerialNum' => SORT_ASC]);
            $entriesDetails = $queryDetails->all();
        }
//        Yii::trace(print_r($generalInfo,true));

        $data['generalInfo'] = $generalInfo;
		$data['results'] = $entriesDetails;

		$response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;

    }

    /**
     * @param string $id ﻿MapStampPicaroUID
     * @return \yii\console\Response|Response
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionGetEquipmentPicaroById($id) {
        try
        {
            $data = [];
            $MapStampPicaroUID = $id;
            $headers = getallheaders();
            WebManagementEquipmentServicesPic::setClient($headers['X-Client']);
            $smRecord = WebManagementEquipmentServicesPic::find()
                ->where(['MapStampPicaroUID' => $MapStampPicaroUID])
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
    public function actionUpdateEquipmentPicaro($id = null) {
        try
        {
            $headers = getallheaders();
            WebManagementEquipmentServicesPic::setClient($headers['X-Client']);

            $put = file_get_contents("php://input");
//            $putData = json_decode($put, true);

//            Yii::trace(PHP_EOL.__CLASS__.' '.__METHOD__.' id = '.$id. ' putData = '.print_r($putData,true));
            $sqlCommand = "EXEC spWebManagementJSON_InspectionServiceUpdate @JSON_Str=:putData";

            $command =  WebManagementEquipmentServicesPic::getDb()->createCommand($sqlCommand);
            $command->bindParam(":putData", $put);

            $result = $command->queryOne();

            Yii::trace(PHP_EOL.__CLASS__.' '.__METHOD__.' result = '.print_r($result,true));

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