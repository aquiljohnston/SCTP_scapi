<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/29/2016
 * Time: 12:45 PM
 */

namespace app\modules\v1\controllers;

use yii\web\Controller;
use yii\web\Response;
use Yii;

class LeakLogController extends Controller {


    public function actionGetServiceMainTable() {
        $row1 = [];
        $row1["Date"] = "4/2/2016";
        $row1["Operator ID"] = "Demo";
        $row1["Inst Type"] = "I-Heath DPIR";
        $row1["Inst Serial #"] = "6717771107";
        $row1["Equip Mode"] = "F";
        $row1["Feet of Main"] = "2020";
        $row1["# of Services"] = "120";
        $row1["Hours"] = "1";

        $row2 = [];
        $row2["Date"] = "4/2/2016";
        $row2["Operator ID"] = "Demo";
        $row2["Inst Type"] = "I-Heath DPIR";
        $row2["Inst Serial #"] = "7786829100";
        $row2["Equip Mode"] = "G";
        $row2["Feet of Main"] = "0";
        $row2["# of Services"] = "0";
        $row2["Hours"] = "1";
        
        $data[] = $row1;
        $data[] = $row2;

        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }

    public function actionGetLeakLogTable() {
        $row1 = [];
        $row1["Status"] = "Reviewed";
        $row1["Approved"] = "";
        $row1["HCA"] = "No";
        $row1["Leak #"] = "1111";
        $row1["SAP Leak #"] = "n/a";
        $row1["Above/Below Ground"] = "A";
        $row1["Reported By"] = "Foot Survey";
        $row1["Date Found"] = "5/10/16";
        $row1["Time Found"] = "11:20";
        $row1["Address City"] = "Concord";
        $row1["Sorl"] = "A";
        $row1["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
        $row1["Reading In %Gas"] = "1.5";
        $row1["Inst Type Found By"] = "I- Heath DPIR";
        $row1["Inst Type Grade By"] = "I- Heath DPIR";
        $row1["Grd"] = "2+";
        $row1["Location Remarks"] = "test";
        $row1["Checkboxes"] = "true";
        
        
        $row2 = [];
        $row2["Status"] = "Reviewed";
        $row2["Approved"] = "";
        $row2["HCA"] = "No";
        $row2["Leak #"] = "1111";
        $row2["SAP Leak #"] = "n/a";
        $row2["Above/Below Ground"] = "A";
        $row2["Reported By"] = "Leg Survey";
        $row2["Date Found"] = "3/10/16";
        $row2["Time Found"] = "1:20";
        $row2["Address City"] = "Concord";
        $row2["Sorl"] = "A";
        $row2["Rec/loc/wm Wim/plt/blk"] = "001/A01/100";
        $row2["Reading In %Gas"] = "1.5";
        $row2["Inst Type Found By"] = "I- Heath DPIR";
        $row2["Inst Type Grade By"] = "I- Heath DPIR";
        $row2["Grd"] = "2+";
        $row2["Location Remarks"] = "test";
        $row2["Checkboxes"] = "true";

        $data[] = $row1;
        $data[] = $row2;

        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }
}