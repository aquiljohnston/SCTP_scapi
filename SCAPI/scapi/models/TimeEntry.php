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
 * @property string $TimeEntryDate
 * @property string $TimeEntryTimeCardID
 * @property integer $TimeEntryActivityID
 * @property string $TimeEntryComment
 * @property string $TimeEntryCreateDate
 * @property string $TimeEntryCreatedBy
 * @property string $TimeEntryModifiedDate
 * @property string $TimeEntryModifiedBy
 *
 * @property ActivityTb $timeEntryActivity
 * @property TimeCardTb $timeEntryTimeCard
 */
class TimeEntry extends \yii\db\ActiveRecord
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
            [['TimeEntryUserID', 'TimeEntryTimeCardID', 'TimeEntryActivityID'], 'integer'],
            [['TimeEntryComment', 'TimeEntryCreatedBy', 'TimeEntryModifiedBy'], 'string']
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
            'TimeEntryTimeCardID' => 'Time Entry Time Card ID',
            'TimeEntryActivityID' => 'Time Entry Activity ID',
            'TimeEntryComment' => 'Time Entry Comment',
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
