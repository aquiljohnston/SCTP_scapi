<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/23/2016
 * Time: 2:08 PM
 */

namespace app\modules\v2\controllers;

use Yii;
use yii\web\Response;
use app\modules\v2\constants\Constants;

trait UpdateMethodNotAllowed {
    /**
     * Default action update replaced with a Method Not Allowed message
     * @return Response 405 JSON response
     */
    public function actionUpdate()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = Constants::METHOD_NOT_ALLOWED;
        $response->setStatusCode(405);
        return $response;
    }
}