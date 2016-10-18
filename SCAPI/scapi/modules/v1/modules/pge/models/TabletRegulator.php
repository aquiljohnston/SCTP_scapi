<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vTabletRegulator".
 *
 * @property string $RegulatorSizeType
 * @property string $RegulatorMfgType
 * @property string $RegulatorModelType
 */
class TabletRegulator extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTabletRegulator';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['RegulatorSizeType'], 'required'],
            [['RegulatorSizeType', 'RegulatorMfgType', 'RegulatorModelType'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'RegulatorSizeType' => 'Regulator Size Type',
            'RegulatorMfgType' => 'Regulator Mfg Type',
            'RegulatorModelType' => 'Regulator Model Type',
        ];
    }
}
