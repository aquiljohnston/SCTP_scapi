<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementOQStatus".
 *
 * @property string $UserLANID
 * @property string $OQ
 * @property string $Status
 * @property string $Expires
 */
class WebManagementOQStatus extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementOQStatus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserLANID', 'OQ', 'Status'], 'string'],
            [['Status'], 'required'],
            [['Expires'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserLANID' => 'User Lanid',
            'OQ' => 'Oq',
            'Status' => 'Status',
            'Expires' => 'Expires',
        ];
    }
}
