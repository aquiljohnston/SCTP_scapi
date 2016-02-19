<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ActivityCodeTb".
 *
 * @property integer $ActivityCodeID
 * @property string $ActivityCodeType
 * @property string $ActivityCodeDescription
 */
class ActivityCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ActivityCodeTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ActivityCodeID'], 'required'],
            [['ActivityCodeID'], 'integer'],
            [['ActivityCodeType', 'ActivityCodeDescription'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ActivityCodeID' => 'Activity Code ID',
            'ActivityCodeType' => 'Activity Code Type',
            'ActivityCodeDescription' => 'Activity Code Description',
        ];
    }
}
