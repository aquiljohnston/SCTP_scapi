<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/24/2016
 * Time: 3:20 PM
 */

namespace app\modules\v1\controllers;
use Yii;
use app\modules\v1\models\BaseActiveRecord;
use yii\web\Response;

trait GetAll
{
    /**
     * Gets all of the subclass's model's records
     *
     * @return Response The records in a JSON format
     * @throws \yii\web\HttpException 400 if any exceptions are thrown
     */
    public function actionGetAll()
    {
        try
        {
            //set model class
            $modelClass = $this->modelClass;

            //set db target
            $headers = getallheaders();
            BaseActiveRecord::setClient($headers['X-Client']);
            $modelClass::setClient($headers['X-Client']);

            $models = $modelClass::find()
                ->all();

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $models;

            return $response;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
    }
}