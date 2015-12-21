<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ActivityTb".
 *
 * @property integer $ActivtyID
 * @property string $ActivityStartTime
 * @property string $ActivityEndTime
 * @property string $ActivityTitle
 * @property string $ActivityBillingCode
 * @property integer $ActivityJobCodeID
 * @property string $ActivityCreateDate
 * @property string $ActivityCreatedBy
 * @property string $ActivityModifiedDate
 * @property string $ActivityModifiedBy
 *
 * @property JobCodeTb $activityJobCode
 * @property MileageEntryTb[] $mileageEntryTbs
 * @property TimeEntryTb[] $timeEntryTbs
 */
class Activity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ActivityTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ActivityStartTime', 'ActivityEndTime', 'ActivityCreateDate', 'ActivityModifiedDate'], 'safe'],
            [['ActivityTitle', 'ActivityBillingCode', 'ActivityCreatedBy', 'ActivityModifiedBy'], 'string'],
            [['ActivityJobCodeID'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ActivtyID' => 'Activty ID',
            'ActivityStartTime' => 'Activity Start Time',
            'ActivityEndTime' => 'Activity End Time',
            'ActivityTitle' => 'Activity Title',
            'ActivityBillingCode' => 'Activity Billing Code',
            'ActivityJobCodeID' => 'Activity Job Code ID',
            'ActivityCreateDate' => 'Activity Create Date',
            'ActivityCreatedBy' => 'Activity Created By',
            'ActivityModifiedDate' => 'Activity Modified Date',
            'ActivityModifiedBy' => 'Activity Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityJobCode()
    {
        return $this->hasOne(JobCodeTb::className(), ['JobCodeID' => 'ActivityJobCodeID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageEntryTbs()
    {
        return $this->hasMany(MileageEntryTb::className(), ['MileageEntryActivityID' => 'ActivtyID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeEntryTbs()
    {
        return $this->hasMany(TimeEntryTb::className(), ['TimeEntryActivityID' => 'ActivtyID']);
    }
}
