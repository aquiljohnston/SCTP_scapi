<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAOCType".
 *
 * @property string $AOCType
 * @property string $WorkCenter
 * @property string $Division
 * @property string $Surveyor
 */
class WebManagementDropDownAOCType extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAOCType';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AOCType', 'WorkCenter', 'Division', 'Surveyor'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AOCType' => 'Aoctype',
            'WorkCenter' => 'Work Center',
            'Division' => 'Division',
            'Surveyor' => 'Surveyor',
        ];
    }
}
