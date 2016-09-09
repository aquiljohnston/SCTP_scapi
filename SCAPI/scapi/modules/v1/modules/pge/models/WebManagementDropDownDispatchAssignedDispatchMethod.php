<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownDispatchAssignedDispatchMethod".
 *
 * @property string $DispatchMethod
 */
class WebManagementDropDownDispatchAssignedDispatchMethod extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownDispatchAssignedDispatchMethod';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['DispatchMethod'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'DispatchMethod' => 'Dispatch Method',
        ];
    }
}
