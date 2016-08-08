<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 8/3/2016
 * Time: 2:40 PM
 */

namespace app\modules\v1\controllers;

use Yii;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use yii\web\Response;

class MapStampMgmtController extends \yii\web\Controller{
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
                    'get-table' => ['get'],
                ],
            ];
        return $behaviors;
    }

    public function actionGetTable($workCenter = null, $surveyor = null, $startDate = null, $endDate = null) {
        $data = [];

        $row1 = [];
        $row1["Division"] = "Diablo";
        $row1["WorkCenter"] = "Cydaea";
        $row1["MapPlat"] = "161-30-5-C";
        $row1["Status"] = "In Progress";
        $row1["Type"] = "TR";
        $row1["# of Days"] = "1";
        $row1["# of Leaks"] = "12";
        $row1["Notification ID"] = "667171777461";
        $row1["Date"] = "05/05/2015";
        $row1["Surveyor"] = "johndoe";
        $row1["Tab"] = "Not Approved";
        $data[] = $row1;

        $row2 = [];
        $row2["Division"] = "Diablo";
        $row2["WorkCenter"] = "Cydaea";
        $row2["MapPlat"] = "354-99-9-A";
        $row2["Status"] = "In Progress";
        $row2["Type"] = "PIC";
        $row2["# of Days"] = "1";
        $row2["# of Leaks"] = "12";
        $row2["Notification ID"] = "667171777461";
        $row2["Date"] = "06/05/2015";
        $row2["Surveyor"] = "bsmith";
        $row2["Tab"] = 'Approved / Not Submitted';
        $data[] = $row2;

        $row3 = [];
        $row3["Division"] = "Diablo";
        $row3["WorkCenter"] = "Cydaea";
        $row3["MapPlat"] = "161-30-5-D";
        $row3["Status"] = "In Progress";
        $row3["Type"] = "TR";
        $row3["# of Days"] = "1";
        $row3["# of Leaks"] = "12";
        $row3["Notification ID"] = "667171777461";
        $row3["Date"] = "05/05/2016";
        $row3['Surveyor'] = 'tclearwater';
        $row3['Tab'] = 'Submitted / Pending';
        $data[] = $row3;

        $row4 = [];
        $row4["Division"] = "Diablo";
        $row4["WorkCenter"] = "Cydaea";
        $row4["MapPlat"] = "161-30-5-D";
        $row4["Status"] = "In Progress";
        $row4["Type"] = "TR";
        $row4["# of Days"] = "1";
        $row4["# of Leaks"] = "12";
        $row4["Notification ID"] = "667171777461";
        $row4["Date"] = "05/05/2016";
        $row4['Surveyor'] = 'tclearwater';
        $row4['Tab'] = 'Exceptions';
        $data[] = $row4;


        $row4 = [];
        $row4["Division"] = "Diablo";
        $row4["WorkCenter"] = "Center1";
        $row4["MapPlat"] = "161-30-5-D";
        $row4["Status"] = "In Progress";
        $row4["Type"] = "TR";
        $row4["# of Days"] = "1";
        $row4["# of Leaks"] = "12";
        $row4["Notification ID"] = "667171777461";
        $row4["Date"] = "05/05/2016";
        $row4['Surveyor'] = 'tclearwater';
        $row4['Tab'] = 'Completed';
        $data[] = $row4;


        $filteredData = [];
        $filteredData['Not Approved'] = [];
        $filteredData['Approved / Not Submitted'] = [];
        $filteredData['Submitted / Pending'] = [];
        $filteredData['Exceptions'] = [];
        $filteredData['Completed'] = [];


        $datesPresent = $startDate != null && $endDate != null;
        for($i = 0; $i < count($data); $i++) {
            if($workCenter == null || $data[$i]['WorkCenter'] == $workCenter) {
                if($surveyor == null || $data[$i]['Surveyor'] == $surveyor) {
                    if(!$datesPresent || BaseActiveController::inDateRange($data[$i]['Date'], $startDate, $endDate)) {
                        $filteredData[$data[$i]['Tab']][] = $data[$i];
                    }
                }
            }
        }


        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $filteredData;
        return $response;
    }

}