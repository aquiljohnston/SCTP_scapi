<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "PayCodeTb".
 *
 * @property integer $PayCodeID
 * @property string $PayCodeType
 * @property string $PayCodeDescription
 * @property string $PayCodeArchiveFlag
 * @property string $PayCodeCreateDate
 * @property string $PayCodeCreatedBy
 * @property string $PayCodeModifiedDate
 * @property string $PayCodeModifiedBy
 */
class PayCode extends BaseActiveRecord
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
            [['PayCodeType', 'PayCodeDescription', 'PayCodeCreatedBy', 'PayCodeModifiedBy', 'PayCodeArchiveFlag'], 'string'],
            [['PayCodeCreateDate', 'PayCodeModifiedDate'], 'safe']
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
			'PayCodeArchiveFlag' => 'Pay Code Archive Flag',
            'PayCodeCreateDate' => 'Pay Code Create Date',
            'PayCodeCreatedBy' => 'Pay Code Created By',
            'PayCodeModifiedDate' => 'Pay Code Modified Date',
            'PayCodeModifiedBy' => 'Pay Code Modified By',
        ];
    }
}
