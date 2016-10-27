<?php

namespace app\modules\v1\modules\pge\controllers;

use app\authentication\TokenAuth;
use Yii;
use yii\filters\VerbFilter;
use yii\rest\Controller;
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

}