<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tMileageEntryEventHistory".
 *
 * @property int $MileageEntryID
 * @property string $MileageEntryStartingMileage
 * @property string $MileageEntryEndingMileage
 * @property string $MileageEntryStartDate
 * @property string $MileageEntryEndDate
 * @property string $MileageEntryWeekDay
 * @property string $MileageEntryType
 * @property int $MileageEntryMileageCardID
 * @property int $MileageEntryActivityID
 * @property string $MileageEntryApprovedBy
 * @property string $MileageEntryComment
 * @property string $MileageEntryCreatedBy
 * @property string $MileageEntryModifiedDate
 * @property string $MileageEntryModifiedBy
 * @property string $MileageEntryUserName
 * @property string $MileageEntrySrcDTLT
 * @property string $MileageEntrySrvDTLT
 * @property int $MileageEntryActiveFlag
 * @property string $ChangeMadeBy
 * @property string $ChangeDateTime
 * @property string $Change
 * @property string $Comments
 */
class MileageEntryEventHistory extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tMileageEntryEventHistory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['MileageEntryID', 'MileageEntryMileageCardID', 'MileageEntryActivityID', 'MileageEntryActiveFlag'], 'integer'],
            [['MileageEntryStartingMileage', 'MileageEntryEndingMileage'], 'number'],
            [['MileageEntryStartDate', 'MileageEntryEndDate', 'MileageEntryModifiedDate', 'MileageEntrySrcDTLT', 'MileageEntrySrvDTLT', 'ChangeDateTime'], 'safe'],
            [['MileageEntryWeekDay', 'MileageEntryType', 'MileageEntryApprovedBy', 'MileageEntryComment', 'MileageEntryCreatedBy', 'MileageEntryModifiedBy', 'MileageEntryUserName', 'ChangeMadeBy', 'Change', 'Comments'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'MileageEntryID' => 'Mileage Entry ID',
            'MileageEntryStartingMileage' => 'Mileage Entry Starting Mileage',
            'MileageEntryEndingMileage' => 'Mileage Entry Ending Mileage',
            'MileageEntryStartDate' => 'Mileage Entry Start Date',
            'MileageEntryEndDate' => 'Mileage Entry End Date',
            'MileageEntryWeekDay' => 'Mileage Entry Week Day',
            'MileageEntryType' => 'Mileage Entry Type',
            'MileageEntryMileageCardID' => 'Mileage Entry Mileage Card ID',
            'MileageEntryActivityID' => 'Mileage Entry Activity ID',
            'MileageEntryApprovedBy' => 'Mileage Entry Approved By',
            'MileageEntryComment' => 'Mileage Entry Comment',
            'MileageEntryCreatedBy' => 'Mileage Entry Created By',
            'MileageEntryModifiedDate' => 'Mileage Entry Modified Date',
            'MileageEntryModifiedBy' => 'Mileage Entry Modified By',
            'MileageEntryUserName' => 'Mileage Entry User Name',
            'MileageEntrySrcDTLT' => 'Mileage Entry Src Dtlt',
            'MileageEntrySrvDTLT' => 'Mileage Entry Srv Dtlt',
            'MileageEntryActiveFlag' => 'Mileage Entry Active Flag',
            'ChangeMadeBy' => 'Change Made By',
            'ChangeDateTime' => 'Change Date Time',
            'Change' => 'Change',
            'Comments' => 'Comments',
        ];
    }
}
