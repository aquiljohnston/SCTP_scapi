<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/23/2016
 * Time: 2:08 PM
 */

namespace app\controllers;

use Yii;
use yii\web\Response;


trait UpdateMethodNotAllowed {
    /**
     * Default action update replaced with a Method Not Allowed message
     * @return Response 405 JSON response
     */
    public function actionUpdate()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = "Method Not Allowed";
        $response->setStatusCode(405);
        return $response;
    }
}