<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/28/2016
 * Time: 12:54 PM
 */
namespace app\modules\v1\controllers;
use \yii\web\Controller;
use \Yii;
use yii\web\Response;

class AOCController extends Controller {

    public function actionGet() {
        $data = [];
        
        
        $gavin = [];
        $gavin["Badge #"] = 56183;
        $gavin["Status"] = "In Progress";
        $gavin["Employee"] = "Free, Gavin";
        $gavin["Date/Time"] = "7/28/16 17:03";
        $gavin["AOC Code"] = "AC";
        $gavin["Meter"] = "235294";
        $gavin["House #"] = "123";
        $gavin["Street"] = "Ashford-Dunwoody Rd";
        $gavin["City"] = "Dunwoody";
        $gavin["State"] = "GA";
        $gavin["Zip"] = "30346";
        $gavin["Images"] = "";
        $gavin["Comment"] = "This is a comment";
        $data[] = $gavin;
        
        $chris = [];
        $chris["Badge"] = "56183";
        $chris["Status"] = "In Progress";
        $chris["Employee"] = "Smith, Chris";
        $chris["Date/Time"] = "5/9/16 8:25";
        $chris["AOC Code"] = "AC";
        $chris["Meter"] = "1235464";
        $chris["House #"] = "100";
        $chris["Street"] = "Northwood Dr";
        $chris["City"] = "Concord";
        $chris["State"] = "CA";
        $chris["Zip"] = "94520-4508";
        $chris["Images"] = "";
        $chris["Comment"] = "This is probably a comment";
        $data[] = $chris;


        $burnie = [];
        $burnie["Badge"] = "402953";
        $burnie["Status"] = "In Progress";
        $burnie["Employee"] = "Berns, Burnie";
        $burnie["Date/Time"] = "6/25/16 8:25";
        $burnie["AOC Code"] = "AC";
        $burnie["Meter"] = "5235464";
        $burnie["House #"] = "400";
        $burnie["Street"] = "Roswell Rd";
        $burnie["City"] = "Sandy Springs";
        $burnie["State"] = "GA";
        $burnie["Zip"] = "30319";
        $burnie["Images"] = "AF4REJN3OI4SI422.jpg";
        $burnie["Comment"] = "Roosters don't have teeth";
        $data[] = $burnie;
        

        //send response
        $response = Yii::$app->response;
        $response ->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }
}