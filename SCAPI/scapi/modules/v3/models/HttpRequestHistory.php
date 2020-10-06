<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "HttpRequestHistory".
 *
 * @property string $Token
 * @property string $Route
 * @property string $Username
 * @property string $RouteType
 * @property string $Headers
 * @property string $Body
 * @property string $Comments
 * @property string $Miscellaneous
 * @property integer $Reason
 */
class HttpRequestHistory extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'HttpRequestHistory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Token', 'Route', 'RouteType', 'Headers', 'Body', 'Comments', 'Miscellaneous'], 'string'],
            [['Reason'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Token' => 'Token',
            'Route' => 'Route',
            'RouteType' => 'Route type',
            'Headers' => 'Headers',
            'Body' => 'Post body',
            'Comments' => 'Comments',
            'Miscellaneous' => 'Error message',
            'Reason' => 'Reason',
        ];
    }

    /**
     * @param \Exception $exception
     */
    public function setExceptionData(\Exception $exception)
    {
        $this->Miscellaneous = $exception->getMessage();    //exception code or error message
        $this->Reason = $exception->getCode();              //e.g. 401, 400, 500
    }

    /**
     * Set data from Request
     */
    public function setRequestData()
    {
        $request = Yii::$app->request;
        $header = $request->headers;

        $headerData = [
            'x-client'          => $header['x-client'] ?: '',
            'content-type'      => $header['content-type'] ?: '',
            'content-length'    => $header['content-length'] ?: '',
            'accept'            => $header['accept'] ?: '',
        ];

        $this->Headers = json_encode($headerData);  //x-client, auth token, etc.
        $this->RouteType = $request->method;        //get, post, put, delete
        $this->Route = $request->url;               //route called including version
        if(!empty($request->rawBody)){
            $this->Body = json_encode($request->rawBody);            //post body if any, url params if any
        } else {
            $getParams = $request->queryParams;
            unset($getParams['r']);
            $this->Body = !empty($getParams) ? json_encode($getParams) : '';
        }
        if(!empty($header['authorization'])){
            $this->Token = substr(base64_decode(explode(" ", $header['authorization'])[1]), 0, -1);
            $history = HistoryAuth_Assignment::find()->where(['Token' => $this->Token])->one();
            $this->Username = !empty($history) ? $history->CreatedBy : '';
        } else {
            $this->Token = '';
            $this->Username = '';
        }
    }
}
