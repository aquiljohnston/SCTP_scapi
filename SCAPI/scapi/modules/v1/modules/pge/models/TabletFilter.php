<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vTabletFilter".
 *
 * @property string $FilterSizeType
 * @property string $FilterMfgType
 * @property string $FilterModelType
 */
class TabletFilter extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTabletFilter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['FilterSizeType', 'FilterMfgType', 'FilterModelType'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'FilterSizeType' => 'Filter Size Type',
            'FilterMfgType' => 'Filter Mfg Type',
            'FilterModelType' => 'Filter Model Type',
        ];
    }
}
