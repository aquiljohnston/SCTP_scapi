<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "AlertTb".
 *
 * @property integer $ID
 * @property string $Title
 * @property string $CreatedDate
 * @property string $Username
 * @property string $ProjectID
 * @property string $Severity
 * @property string $SvrDTLT
 * @property string $svrDTLTOffset
 * @property string $Message
 */
class Alert extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AlertTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Title', 'Username', 'ProjectID', 'Severity', 'Message'], 'string'],
            [['CreatedDate', 'SvrDTLT', 'svrDTLTOffset'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Title' => 'Title',
            'CreatedDate' => 'Created Date',
            'Username' => 'Username',
            'ProjectID' => 'Project ID',
            'Severity' => 'Severity',
            'SvrDTLT' => 'Svr Dtlt',
            'svrDTLTOffset' => 'Svr Dtltoffset',
            'Message' => 'Message',
        ];
    }
}
