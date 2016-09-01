<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownDispatchStatus".
 *
 * @property string $Status
 */
class WebManagementDropDownDispatchStatus extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownDispatchStatus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Status'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Status' => 'Status',
        ];
    }
}
