<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/23/2016
 * Time: 1:58 PM
 */

namespace app\modules\v2\controllers;

use Yii;
use yii\web\Response;

trait ViewMethodNotAllowed {
    /**
     * Default action view replaced with a Method Not Allowed message
     * @return Response 405 JSON response
     */
    public function actionView()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = "Method Not Allowed";
        $response->setStatusCode(405);
        return $response;
    }
}