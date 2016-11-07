<?php

namespace app\modules\v1\modules\pge\controllers;

use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\modules\pge\models\Report;
use Yii;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ReportsController extends Controller {
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
                    'get-report-stub' => ['get']
                ],
            ];
        return $behaviors;
    }

    public function actionGetStub() {
        $data[] = ['Column1', 'Column2', 'Column3'];
        for($i = 1; $i <= 30; $i++) {
            $data[] = ["Row$i", "Data $i - 1", "Data $i - 2"];
        }
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }

    public function actionGetReportDropDown() {

        $headers = getallheaders();
        Report::setClient($headers['X-Client']);

        $result = Report::find()
            ->select('ReportDisplayName, ReportSPName, ParmDateFlag, ParmDateOverrideFlag, ParmBetweenDateFlag, ExportFlag, ParmInspectorFlag, ParmDropDownFlag, Parm')
            ->where(['ActiveFlag' => 1])
            ->asArray()
            ->all();

        $result['reports'] = $result;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $result;
        return $response;
    }
    /*
     * todo:　need to call SP or View to get report data
     * @return Json format
     * {
            "data": [],
            "columns": [{
                "title": "Report Date"
            }, {
                "title": "Report Title"
            }, {
                "title": "COMPANY_NO"
            }, {
                "title": "INSP_TYPE"
            }, {
                "title": "Survey_Period"
            }, {
                "title": "Completed_Asset_Count"
            }]
        }
     */
    public function actionGetReport(){

    }

    /*
     * todo: get Parm Drop Down list
     * @return Json format
     */
    public function actionGetParmDropDown(){

    }

    public function actionGet($report) {
        BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
        $UID = BaseActiveController::getUserFromToken()->UserUID;

        $headers = getallheaders();
        BaseActiveRecord::setClient($headers['X-Client']);

        $connection = BaseActiveRecord::getDb();
        /*
         * TODO:
         * Verify report name as valid to prevent unwanted execution.
         * Possibilities include
         * - checking against a hardcoded list (hard to maintain)
         * - [preferred] checking for existence in rReport (hit on performance)
         */
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $connection
                ->createCommand("SET NOCOUNT ON EXEC udsp_rptTest_v01 :param1, :param2,")
                ->queryAll(array(':param1' => 1, ':param2' => '[7 - Year to Today]'));
        return $response;
    }
}