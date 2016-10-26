<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 8/3/2016
 * Time: 2:40 PM
 */

namespace app\modules\v1\modules\pge\controllers;

use app\modules\v1\controllers\BaseActiveController;
use Yii;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v1\modules\pge\models\WebManagementMapStampManagement;
use yii\data\Pagination;


class MapStampController extends \yii\web\Controller {
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
                    'get-detail' => ['get']
                ],
            ];
        return $behaviors;
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
//                        ['like', 'TotalNoOfDays', $search],
                        ['like', 'Total # Of Days', $search],
                        ['like', 'TotalNoOfLeaks', $search],
                        ['like', 'TotalFeetOfMain', $search],
                        ['like', 'TotalServices', $search],
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    $query->andWhere(['between', 'ComplianceDate', $startDate, $endDate]);
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
        $tableData = [];
        $data = [];
        $info = [];
		
        $info['Id'] = 1;
        $info['Status'] = 'Reviewed';
        $info['SurveyArea'] = '1';
        $info['SurveyType'] = 'TR';
        $info['DateSurveyed'] = '08/23/2016';
        $info['SurveyorLanID'] = 'PGE4';
        $info['InstType'] = 'DPIR';
        $info['InstSerialNum'] = 'GI_900121821';
        $info['WindSpeedStart'] = 10;
        $info['WindSpeedMid'] = 12;
        $info['Foot'] = false;
        $info['Mobile'] = true;
        $info['FeetOfMain'] = 3450;
        $info['NumOfService'] = 56;

        $tableData[] = $info;

        $info2['Id'] = 2;
        $info2['Status'] = 'Reviewed';
        $info2['SurveyArea'] = '1';
        $info2['SurveyType'] = 'TR';
        $info2['DateSurveyed'] = '08/23/2016';
        $info2['SurveyorLanID'] = 'PGE5';
        $info2['InstType'] = 'DPIR';
        $info2['InstSerialNum'] = 'GI_907161841';
        $info2['WindSpeedStart'] = 10;
        $info2['WindSpeedMid'] = 12;
        $info2['Foot'] = true;
        $info2['Mobile'] = false;
        $info2['FeetOfMain'] = 1311;
        $info2['NumOfService'] = 93;

        $tableData[] = $info2;


        $info3['Id'] = 3;
        $info3['Status'] = 'In Progress';
        $info3['SurveyArea'] = '1';
        $info3['SurveyType'] = 'TR';
        $info3['DateSurveyed'] = '08/23/2016';
        $info3['SurveyorLanID'] = 'PGE5';
        $info3['InstType'] = 'DPIR';
        $info3['InstSerialNum'] = 'GI_907161841';
        $info3['WindSpeedStart'] = 10;
        $info3['WindSpeedMid'] = 12;
        $info3['Foot'] = true;
        $info3['Mobile'] = false;
        $info3['FeetOfMain'] = 1311;
        $info3['NumOfService'] = 93;

        $tableData[] = $info3;

        $data['TableData'] = [];
        $data['Status'] = "Not Approved";
        $data['PICTotalFeetOfMain'] = 103574;
		$data['PICTotalServices'] = 497;
        $data['TotalFeetOfMain'] = 207295;
        $data['TotalServices'] = 1100;

        foreach($tableData as $item) {
            if($item['Id'] == $id) { // We want loose equals
                $data['TableData'][] = $item;
            }
        }
		
		$response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;

    }
}