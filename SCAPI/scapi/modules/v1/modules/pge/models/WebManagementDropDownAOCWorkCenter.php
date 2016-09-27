<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAOCWorkCenter".
 *
 * @property string $WorkCenter
 * @property string $Division
 */
class WebManagementDropDownAOCWorkCenter extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAOCWorkCenter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WorkCenter', 'Division'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'WorkCenter' => 'Work Center',
            'Division' => 'Division',
        ];
    }
}
