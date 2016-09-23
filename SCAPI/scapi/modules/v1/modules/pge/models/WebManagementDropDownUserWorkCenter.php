<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownUserWorkCenter".
 *
 * @property string $WorkCenter
 * @property string $WorkCenterUID
 */
class WebManagementDropDownUserWorkCenter extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownUserWorkCenter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WorkCenter', 'WorkCenterUID'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'WorkCenter' => 'Work Center',
            'WorkCenterUID' => 'Work Center Uid',
        ];
    }
}
