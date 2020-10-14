<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "MileageRate".
 *
 * @property int $Id
 * @property string $MileageType
 * @property double $Rate
 * @property int $IsActive
 * @property string $CreatedDate
 * @property string $ModifiedDate
 */
class MileageRate extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MileageRate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['MileageType', 'Rate'], 'required'],
            [['MileageType'], 'string'],
            [['Rate'], 'number'],
            [['IsActive'], 'integer'],
            [['CreatedDate', 'ModifiedDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'MileageType' => 'Mileage Type',
            'Rate' => 'Rate',
            'IsActive' => 'Is Active',
            'CreatedDate' => 'Created Date',
            'ModifiedDate' => 'Modified Date',
        ];
    }
}
