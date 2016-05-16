<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ActivityTb".
 *
 * @property integer $ActivityID
 * @property string $ActivityStartTime
 * @property string $ActivityEndTime
 * @property string $ActivityTitle
 * @property string $ActivityBillingCode
 * @property integer $ActivityCode
 * @property integer $ActivityPayCode
 * @property double $ActivityLatitude
 * @property double $ActivityLongitude
 * @property string $ActivityCreateDate
 * @property string $ActivityCreatedBy
 * @property string $ActivityModifiedDate
 * @property string $ActivityModifiedBy
 *
 * @property MileageEntryTb[] $mileageEntryTbs
 * @property TimeEntryTb[] $timeEntryTbs
 */
class Activity extends BaseActiveRecord
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
            [['ActivityTitle', 'ActivityBillingCode'], 'string'],
            [['ActivityCode', 'ActivityPayCode' , 'ActivityCreatedBy', 'ActivityModifiedBy'], 'integer'],
			[['ActivityLatitude', 'ActivityLongitude'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ActivityID' => 'Activity ID',
            'ActivityStartTime' => 'Activity Start Time',
            'ActivityEndTime' => 'Activity End Time',
            'ActivityTitle' => 'Activity Title',
            'ActivityBillingCode' => 'Activity Billing Code',
            'ActivityCode' => 'Activity Code',
            'ActivityPayCode' => 'Activity Pay Code',
            'ActivityCreateDate' => 'Activity Create Date',
            'ActivityCreatedBy' => 'Activity Created By',
            'ActivityModifiedDate' => 'Activity Modified Date',
            'ActivityModifiedBy' => 'Activity Modified By',
			'ActivityLatitude' => 'Activity Latitude',
			'ActivityLongitude' => 'Activity Longitude',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageEntryTbs()
    {
        return $this->hasMany(MileageEntryTb::className(), ['MileageEntryActivityID' => 'ActivityID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeEntryTbs()
    {
        return $this->hasMany(TimeEntryTb::className(), ['TimeEntryActivityID' => 'ActivityID']);
    }
}
