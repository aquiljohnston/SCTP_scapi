<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAOCDivision".
 *
 * @property string $Division
 */
class WebManagementDropDownAOCDivision extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAOCDivision';
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
