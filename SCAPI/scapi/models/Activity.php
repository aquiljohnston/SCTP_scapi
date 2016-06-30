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
 * @property string $ActivityStartDateTime
 * @property string $ActivityEndDateTime
 * @property string $ActivityArchiveFlag
 * @property string $ActivityCreateDate
 * @property integer $ActivityCreatedUserUID
 * @property string $ActivityModifiedDate
 * @property integer $ActivityModifiedUserUID
 * @property string $ActivityUID
 * @property integer $ActivityProjectID
 * @property string $ActivitySourceID
 * @property string $ActivitySrvDTLT
 * @property string $ActivitySrvDTLTOffset
 * @property string $ActivitySrcDTLT
 * @property string $ActivityGPSType
 * @property string $ActivityGPSSentence
 * @property string $ActivityShape
 * @property string $ActivityComments
 * @property string $ActivityBatteryLevel
 * @property string $ActivityRevisionComments
 * @property string $ActivityElapsedSec
 *
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
            [['ActivityStartTime', 'ActivityEndTime', 'ActivityStartDateTime', 'ActivityEndDateTime', 'ActivityCreateDate', 'ActivityModifiedDate', 'ActivitySrvDTLT', 'ActivitySrvDTLTOffset', 'ActivitySrcDTLT'], 'safe'],
            [['ActivityTitle', 'ActivityBillingCode', 'ActivityArchiveFlag', 'ActivityUID', 'ActivitySourceID', 'ActivityGPSType', 'ActivityGPSSentence', 'ActivityShape', 'ActivityComments', 'ActivityBatteryLevel', 'ActivityRevisionComments', 'ActivityElapsedSec'], 'string'],
            [['ActivityCode', 'ActivityPayCode', 'ActivityCreatedUserUID', 'ActivityModifiedUserUID', 'ActivityProjectID'], 'integer'],
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
            'ActivityLatitude' => 'Activity Latitude',
            'ActivityLongitude' => 'Activity Longitude',
            'ActivityStartDateTime' => 'Activity Start Date Time',
            'ActivityEndDateTime' => 'Activity End Date Time',
            'ActivityArchiveFlag' => 'Activity Archive Flag',
            'ActivityCreateDate' => 'Activity Create Date',
            'ActivityCreatedUserUID' => 'Activity Created User Uid',
            'ActivityModifiedDate' => 'Activity Modified Date',
            'ActivityModifiedUserUID' => 'Activity Modified User Uid',
            'ActivityUID' => 'Activity Uid',
            'ActivityProjectID' => 'Activity Project ID',
            'ActivitySourceID' => 'Activity Source ID',
            'ActivitySrvDTLT' => 'Activity Srv Dtlt',
            'ActivitySrvDTLTOffset' => 'Activity Srv Dtltoffset',
            'ActivitySrcDTLT' => 'Activity Src Dtlt',
            'ActivityGPSType' => 'Activity Gpstype',
            'ActivityGPSSentence' => 'Activity Gpssentence',
            'ActivityShape' => 'Activity Shape',
            'ActivityComments' => 'Activity Comments',
            'ActivityBatteryLevel' => 'Activity Battery Level',
            'ActivityRevisionComments' => 'Activity Revision Comments',
            'ActivityElapsedSec' => 'Activity Elapsed Sec',
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
