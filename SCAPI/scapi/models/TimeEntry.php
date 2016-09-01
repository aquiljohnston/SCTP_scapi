<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TimeEntryTb".
 *
 * @property integer $TimeEntryID
 * @property integer $TimeEntryUserID
 * @property string $TimeEntryStartTime
 * @property string $TimeEntryEndTime
 * @property string $TimeEntryWeekDay
 * @property string $TimeEntryDate
 * @property string $TimeEntryActiveFlag
 * @property string $TimeEntryHours
 * @property integer $TimeEntryMinutes
 * @property integer $TimeEntryTimeCardID
 * @property integer $TimeCardFK
 * @property integer $TimeEntryActivityID
 * @property string $TimeEntryComment
 * @property string $TimeEntryArchiveFlag
 * @property string $TimeEntryCreateDate
 * @property string $TimeEntryCreatedBy
 * @property string $TimeEntryModifiedDate
 * @property string $TimeEntryModifiedBy
 *
 * @property ActivityTb $timeEntryActivity
 * @property TimeCardTb $timeEntryTimeCard
 */
class TimeEntry extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TimeEntryTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['TimeEntryStartTime', 'TimeEntryEndTime', 'TimeEntryDate', 'TimeEntryCreateDate', 'TimeEntryModifiedDate'], 'safe'],
            [['TimeEntryUserID', 'TimeEntryMinutes', 'TimeEntryTimeCardID', 'TimeEntryActivityID', 'TimeEntryCreatedBy', 'TimeEntryModifiedBy', 'TimeCardFK'], 'integer'],
            [['TimeEntryComment', 'TimeEntryActiveFlag', 'TimeEntryWeekDay', 'TimeEntryHours', 'TimeEntryArchiveFlag'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'TimeEntryID' => 'Time Entry ID',
			'TimeEntryUserID' => 'Time Entry User ID',
            'TimeEntryStartTime' => 'Time Entry Start Time',
            'TimeEntryEndTime' => 'Time Entry End Time',
            'TimeEntryDate' => 'Time Entry Date',
			'TimeEntryWeekDay' => 'Time Entry Week Day',
			'TimeEntryActiveFlag' => 'Time Entry Active Flag',
			'TimeEntryHours' => 'Time Entry Hours',
			'TimeEntryMinutes' => 'Time Entry Minutes',
            'TimeEntryTimeCardID' => 'Time Entry Time Card ID',
			'TimeCardFK' => 'Time Card FK',
            'TimeEntryActivityID' => 'Time Entry Activity ID',
            'TimeEntryComment' => 'Time Entry Comment',
			'TimeEntryArchiveFlag' => 'Time Entry Archive Flag',
            'TimeEntryCreateDate' => 'Time Entry Create Date',
            'TimeEntryCreatedBy' => 'Time Entry Created By',
            'TimeEntryModifiedDate' => 'Time Entry Modified Date',
            'TimeEntryModifiedBy' => 'Time Entry Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeEntryActivity()
    {
        return $this->hasOne(ActivityTb::className(), ['ActivtyID' => 'TimeEntryActivityID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeEntryTimeCard()
    {
        return $this->hasOne(TimeCardTb::className(), ['TimeCardID' => 'TimeEntryTimeCardID']);
    }
}
