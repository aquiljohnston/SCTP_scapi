<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "PayCodeTb".
 *
 * @property integer $PayCodeID
 * @property string $PayCodeType
 * @property string $PayCodeDescription
 */
class PayCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'PayCodeTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['PayCodeID'], 'integer'],
            [['PayCodeType', 'PayCodeDescription'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'PayCodeID' => 'Pay Code ID',
            'PayCodeType' => 'Pay Code Type',
            'PayCodeDescription' => 'Pay Code Description',
        ];
    }
}
