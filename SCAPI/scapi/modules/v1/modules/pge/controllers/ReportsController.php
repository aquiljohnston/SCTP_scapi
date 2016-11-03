<?php

namespace app\modules\v1\modules\pge\controllers;

use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;
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

    public function actionGetReports() {
        BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
        $UID = BaseActiveController::getUserFromToken()->UserUID;

        $headers = getallheaders();
        BaseActiveRecord::setClient($headers['X-Client']);

        $connection = BaseActiveRecord::getDb();
        //TODO: Set DB for query
        //$sql = "SELECT [ActiveFlag], [ReportDisplayName], [ReportSPName],[ReportDescription] FROM [vCAT_PGE_GIS_DEV].[dbo].[rReport] Where [ActiveFlag] = 1";
        $result = (new \yii\db\Query())
            ->select(['ActiveFlag', 'ReportDisplayName', 'ReportSPName', 'ReportDescription'])
            ->from('rReport')
            ->where(['ActiveFlag' => 1])
            ->all();

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $result;
        return $response;
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