<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tTimeEntryEventHistory".
 *
 * @property int $ID
 * @property int $TimeEntryID
 * @property int $TimeEntryUserID
 * @property string $TimeEntryStartTime
 * @property string $TimeEntryEndTime
 * @property string $TimeEntryWeekDay
 * @property int $TimeEntryTimeCardID
 * @property int $TimeEntryActivityID
 * @property string $TimeEntryComment
 * @property string $TimeEntryCreatedBy
 * @property string $TimeEntryModifiedDate
 * @property string $TimeEntryModifiedBy
 * @property string $TimeEntryUserName
 * @property string $TimeEntrySrcDTLT
 * @property string $TimeEntrySvrDTLT
 * @property int $TimeEntryActiveFlag
 * @property string $TimeEntryChartOfAccount
 * @property string $ChangeMadeBy
 * @property string $ChangeDateTime
 * @property string $Change
 * @property string $Comments
 */
class TimeEntryEventHistory extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tTimeEntryEventHistory';
    }
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TimeEntryID', 'TimeEntryUserID', 'TimeEntryTimeCardID', 'TimeEntryActivityID', 'TimeEntryActiveFlag'], 'integer'],
            [['TimeEntryStartTime', 'TimeEntryEndTime', 'TimeEntryModifiedDate', 'TimeEntrySrcDTLT', 'TimeEntrySvrDTLT', 'ChangeDateTime'], 'safe'],
            [['TimeEntryWeekDay', 'TimeEntryComment', 'TimeEntryCreatedBy', 'TimeEntryModifiedBy', 'TimeEntryUserName', 'TimeEntryChartOfAccount', 'ChangeMadeBy', 'Change', 'Comments'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
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
            'TimeEntryChartOfAccount' => 'Time Entry Chart Of Account',
            'ChangeMadeBy' => 'Change Made By',
            'ChangeDateTime' => 'Change Date Time',
            'Change' => 'Change',
            'Comments' => 'Comments',
        ];
    }
}
