<?php

namespace app\modules\v3\models;

use Yii;
use function base64_decode;
use function json_encode;
use function str_replace;

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
 * @property string $Username
 * @property integer $Reason
 */
class HttpRequestHistory extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @var bool
     */
    public $ignoreBody = false;

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
            [['Token', 'Route', 'RouteType', 'Headers', 'Body', 'Comments', 'Miscellaneous', 'Username'], 'string'],
            [['Reason', 'ignoreBody'], 'integer'],
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
            'x-client'       => $header['x-client'] ?: '',
            'content-type'   => $header['content-type'] ?: '',
            'content-length' => $header['content-length'] ?: '',
            'accept'         => $header['accept'] ?: '',
        ];

        $this->Headers = json_encode($headerData);  //x-client, auth token, etc.
        $this->RouteType = $request->method;        //get, post, put, delete
        $this->Route = $request->url;               //route called including version
}
