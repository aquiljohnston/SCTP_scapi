<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/23/2016
 * Time: 2:10 PM
 */

namespace app\modules\v1\controllers;

use Yii;
use yii\web\Response;

trait DeleteMethodNotAllowed {
    /**
     * Default action delete replaced with a Method Not Allowed message
     * @return Response 405 JSON response
     */
    public function actionDelete()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = "Method Not Allowed";
        $response->setStatusCode(405);
        return $response;
    }
}