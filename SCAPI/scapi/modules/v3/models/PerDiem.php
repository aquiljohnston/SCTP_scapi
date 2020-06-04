<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "refPerDiem".
 *
 * @property int $ID
 * @property string|null $Label
 * @property string|null $Description
 * @property int|null $No Of Days
 * @property float|null $Rate
 */
class PerDiem extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'refPerDiem';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ctDevDb');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Label', 'Description'], 'string'],
            [['No Of Days'], 'integer'],
            [['Rate'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Label' => 'Label',
            'Description' => 'Description',
            'No Of Days' => 'No  Of  Days',
            'Rate' => 'Rate',
        ];
    }
}
