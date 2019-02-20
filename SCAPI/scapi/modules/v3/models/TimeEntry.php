<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "TimeEntryTb".
 *
 * @property integer $TimeEntryID
 * @property integer $TimeEntryUserID
 * @property string $TimeEntryStartTime
 * @property string $TimeEntryEndTime
 * @property string $TimeEntryWeekDay
 * @property integer $TimeEntryTimeCardID
 * @property integer $TimeEntryActivityID
 * @property string $TimeEntryComment
 * @property string $TimeEntryCreatedBy
 * @property string $TimeEntryModifiedDate
 * @property string $TimeEntryModifiedBy
 * @property string $TimeEntryUserName
 * @property string $TimeEntrySrcDTLT
 * @property string $TimeEntrySvrDTLT
 * @property integer $TimeEntryActiveFlag
 */
class TimeEntry extends \app\modules\v3\models\BaseActiveRecord
{
	const SQL_CONSTRAINT_MESSAGE = 'The combination of Time Entry User ID, Time Entry Start Time and Time Entry End Time has already been taken.';
	
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
            [['TimeEntryUserID', 'TimeEntryTimeCardID', 'TimeEntryActivityID', 'TimeEntryActiveFlag'], 'integer'],
            [['TimeEntryStartTime', 'TimeEntryEndTime', 'TimeEntryModifiedDate', 'TimeEntrySrcDTLT', 'TimeEntrySvrDTLT'], 'safe'],
            [['TimeEntryWeekDay', 'TimeEntryComment', 'TimeEntryCreatedBy', 'TimeEntryModifiedBy', 'TimeEntryUserName'], 'string'],
            [['TimeEntryEndTime', 'TimeEntryStartTime', 'TimeEntryUserID'], 'unique', 'targetAttribute' => ['TimeEntryEndTime', 'TimeEntryStartTime', 'TimeEntryUserID'], 'message' => SELF::SQL_CONSTRAINT_MESSAGE],
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
            'TimeEntryWeekDay' => 'Time Entry Week Day',
            'TimeEntryTimeCardID' => 'Time Entry Time Card ID',
            'TimeEntryActivityID' => 'Time Entry Activity ID',
            'TimeEntryComment' => 'Time Entry Comment',
            'TimeEntryCreatedBy' => 'Time Entry Created By',
            'TimeEntryModifiedDate' => 'Time Entry Modified Date',
            'TimeEntryModifiedBy' => 'Time Entry Modified By',
            'TimeEntryUserName' => 'Time Entry User Name',
            'TimeEntrySrcDTLT' => 'Time Entry Src Dtlt',
            'TimeEntrySvrDTLT' => 'Time Entry Svr Dtlt',
            'TimeEntryActiveFlag' => 'Time Entry Active Flag',
        ];
    }
}
