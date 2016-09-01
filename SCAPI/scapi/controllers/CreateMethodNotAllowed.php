<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/23/2016
 * Time: 2:07 PM
 */

namespace app\controllers;

use Yii;
use yii\web\Response;

trait CreateMethodNotAllowed {
    /**
     * Default action create replaced with a Method Not Allowed message
     * @return Response 405 JSON response
     */
    public function actionCreate()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = "Method Not Allowed";
        $response->setStatusCode(405);
        return $response;
    }
}