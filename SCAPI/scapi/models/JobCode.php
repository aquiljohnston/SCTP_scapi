<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "JobCodeTb".
 *
 * @property integer $JobCodeID
 * @property string $JobCodeCode
 * @property string $JobCodeDescription
 * @property string $JobCodeStatus
 * @property string $JobCodeComments
 * @property string $JobCodeArchiveFlag
 * @property string $JobCodeCreateDate
 * @property string $JobCodeCreatedBy
 * @property string $JobCodeModifiedDate
 * @property string $JobCodeModifiedBy
 *
 * @property ActivityTb[] $activityTbs
 */
class JobCode extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'JobCodeTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['JobCodeCode', 'JobCodeDescription', 'JobCodeStatus', 'JobCodeComments', 'JobCodeCreatedBy', 'JobCodeModifiedBy', 'JobCodeArchiveFlag'], 'string'],
            [['JobCodeCreateDate', 'JobCodeModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'JobCodeID' => 'Job Code ID',
            'JobCodeCode' => 'Job Code Code',
            'JobCodeDescription' => 'Job Code Description',
            'JobCodeStatus' => 'Job Code Status',
            'JobCodeComments' => 'Job Code Comments',
			'JobCodeArchiveFlag' => 'Job Code Archive Flag',
            'JobCodeCreateDate' => 'Job Code Create Date',
            'JobCodeCreatedBy' => 'Job Code Created By',
            'JobCodeModifiedDate' => 'Job Code Modified Date',
            'JobCodeModifiedBy' => 'Job Code Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityTbs()
    {
        return $this->hasMany(ActivityTb::className(), ['ActivityJobCodeID' => 'JobCodeID']);
    }
}
