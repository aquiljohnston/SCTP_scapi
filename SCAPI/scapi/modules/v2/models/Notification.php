<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vNotification".
 *
 * @property integer $ID
 * @property integer $UserID
 * @property integer $NotificationType
 * @property String $SrvDTLT
 */
class Notification extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vNotification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID'], ['UserID'], ['NotificationType'],'integer'],
            [['UserID'], 'required'],
            [['SrvDTLT'], 'safe']
        ];
    }
}
