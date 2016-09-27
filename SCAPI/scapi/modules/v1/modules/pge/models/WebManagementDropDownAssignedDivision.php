<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAssignedDivision".
 *
 * @property string $Division
 */
class WebManagementDropDownAssignedDivision extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAssignedDivision';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Division'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Division' => 'Division',
        ];
    }
}
