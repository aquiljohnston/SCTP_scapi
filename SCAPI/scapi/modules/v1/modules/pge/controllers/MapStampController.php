<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 8/3/2016
 * Time: 2:40 PM
 */

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class MapStampController extends \yii\web\Controller{
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
                    'get-detail' => ['get']
                ],
            ];
        return $behaviors;
    }

    public function actionGetTable($workCenter = null, $surveyor = null, $startDate = null, $endDate = null) {
        
		try
		{
			$data = [];

			$row1 = [];
			$row1["Division"] = "Diablo";
			$row1["WorkCenter"] = "Izual";
			$row1["MapPlat"] = "161-30-5-C";
			$row1["Status"] = "In Progress";
			$row1["Type"] = "1 YR";
			$row1["# of Days"] = "1";
			$row1["# of Leaks"] = "12";
			$row1["Notification ID"] = "667171777461";
			$row1["Date"] = "08/05/2016";
			$row1["Surveyor"] = "johndoe";
			$row1["Tab"] = "Not Approved";
			$data[] = $row1;

			$row2 = [];
			$row2["Division"] = "Diablo";
			$row2["WorkCenter"] = "Izual";
			$row2["MapPlat"] = "161-30-5-C";
			$row2["Status"] = "In Progress";
			$row2["Type"] = "3 YR";
			$row2["# of Days"] = "1";
			$row2["# of Leaks"] = "12";
			$row2["Notification ID"] = "667171777461";
			$row2["Date"] = "08/05/2016";
			$row2["Surveyor"] = "janedoe";
			$row2["Tab"] = "Approved / Not Submitted";
			$data[] = $row2;

			$row3 = [];
			$row3["Division"] = "Azmodan";
			$row3["WorkCenter"] = "Cydaea";
			$row3["MapPlat"] = "141-31-3-C";
			$row3["Status"] = "In Progress";
			$row3["Type"] = "5 YR";
			$row3["# of Days"] = "1";
			$row3["# of Leaks"] = "12";
			$row3["Notification ID"] = "667171777461";
			$row3["Date"] = "05/05/2016";
			$row3["Surveyor"] = "bob1";
			$row3["Tab"] = "Submitted / Pending";
			$data[] = $row3;

			$row4 = [];
			$row4["Division"] = "Malthael";
			$row4["WorkCenter"] = "Urzael";
			$row4["MapPlat"] = "141-31-3-C";
			$row4["Status"] = "In Progress";
			$row4["Type"] = "TR";
			$row4["# of Days"] = "1";
			$row4["# of Leaks"] = "12";
			$row4["Notification ID"] = "667171777461";
			$row4["Date"] = "05/05/2016";
			$row4['Surveyor'] = 'bill2';
			$row4['Tab'] = 'Exceptions';
			$data[] = $row4;


			$row4 = [];
			$row4["Division"] = "Malthael";
			$row4["WorkCenter"] = "Urzael";
			$row4["MapPlat"] = "110-11-3-A";
			$row4["Status"] = "In Progress";
			$row4["Type"] = "3 YR";
			$row4["# of Days"] = "1";
			$row4["# of Leaks"] = "12";
			$row4["Notification ID"] = "667171777461";
			$row4["Date"] = "05/05/2016";
			$row4['Surveyor'] = 'fred3';
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
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
    }
    public function actionGetDetail($id) {
        if($id === "") {
            throw new BadRequestHttpException("Empty ID argument");
        }
        $data = [];
        $info = [];
        $info['Map Number'] = '0001-A01';
        $info['Schedule Month/Year'] = '02/2016';
        $info['Previous Start Date'] = '05/11/2011';
        $info['Current Start Date'] = '02/13/2016';
        $info['Prior Feet of Main'] = '270,000';
        $info['Prior Services'] = 1000;
        $info['Survey Frequency Type'] = '5 Year';
        $info['Notification ID'] = 66717172894967;
        $info['Status'] = 'Completed';

        $data['info'] = $info;

        $tableData = [];
        $row1['Survey Area'] = 1;
        $row1['Type +'] = 'PIC';
        $row1['Date Surveyed'] = '03/13/2016';
        $row1['Surveyor LANID'] = 'JSFT';
        $row1['Instrument'] = 'PIC 46781046';
        $row1['Start Wind Speed'] = 2;
        $row1['Mid-Day Wind Speed'] = 4;
        $row1['Foot'] = true;
        $row1['Mobile'] = true;
        $row1['Feet of Main'] = 18845;
        $row1['Services'] = 100;
        $tableData[] = $row1;

        $row2['Survey Area'] = 2;
        $row2['Type +'] = 'LISA';
        $row2['Date Surveyed'] = '03/13/2016';
        $row2['Surveyor LANID'] = 'JSFT';
        $row2['Instrument'] = 'PIC 758910461';
        $row2['Start Wind Speed'] = 2;
        $row2['Mid-Day Wind Speed'] = 4;
        $row2['Foot'] = true;
        $row2['Mobile'] = false;
        $row2['Feet of Main'] = 18845;
        $row2['Services'] = 100;
        $tableData[] = $row2;

        $data['Table Data'] = $tableData;
        $data['Total Feet of Main'] = 207295;
        $data['Total Services'] = 1100;


        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }
}