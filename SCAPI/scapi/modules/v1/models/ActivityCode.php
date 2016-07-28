<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ActivityCodeTb".
 *
 * @property integer $ActivityCodeID
 * @property string $ActivityCodeType
 * @property string $ActivityCodeDescription
 * @property string $ActivityArchiveFlag
 * @property string $ActivityCodeCreateDate
 * @property string $ActivityCodeCreatedBy
 * @property string $ActivityModifiedDate
 * @property string $ActivityModifiedBy
 */
class ActivityCode extends BaseActiveRecord
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
            [['ActivityCodeType', 'ActivityCodeDescription', 'ActivityCodeCreatedBy', 'ActivityModifiedBy', 'ActivityArchiveFlag'], 'string'],
            [['ActivityCodeCreateDate', 'ActivityModifiedDate'], 'safe']
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
			'ActivityArchiveFlag' => 'Activity Archive Flag',
            'ActivityCodeCreateDate' => 'Activity Code Create Date',
            'ActivityCodeCreatedBy' => 'Activity Code Created By',
            'ActivityModifiedDate' => 'Activity Modified Date',
            'ActivityModifiedBy' => 'Activity Modified By',
        ];
    }
}
