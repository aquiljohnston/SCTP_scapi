<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "MileageEntryTb".
 *
 * @property integer $MileageEntryID
 * @property string $MileageEntryStartingMileage
 * @property string $MileageEntryEndingMileage
 * @property string $MileageEntryStartDate
 * @property string $MileageEntryEndDate
 * @property string $MileageEntryWeekDay
 * @property string $MileageEntryType
 * @property integer $MileageEntryMileageCardID
 * @property integer $MileageEntryActivityID
 * @property string $MileageEntryApprovedBy
 * @property string $MileageEntryComment
 * @property string $MileageEntryCreatedBy
 * @property string $MileageEntryModifiedDate
 * @property string $MileageEntryModifiedBy
 * @property string $MileageEntryUserName
 * @property string $MileageEntrySrcDTLT
 * @property string $MileageEntrySrvDTLT
 * @property integer $MileageEntryActiveFlag
 *
 * @property MileageCardTb $mileageEntryMileageCard
 */
class MileageEntry extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MileageEntryTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MileageEntryStartingMileage', 'MileageEntryEndingMileage'], 'number'],
            [['MileageEntryStartDate', 'MileageEntryEndDate', 'MileageEntryModifiedDate', 'MileageEntrySrcDTLT', 'MileageEntrySrvDTLT'], 'safe'],
            [['MileageEntryWeekDay', 'MileageEntryType', 'MileageEntryApprovedBy', 'MileageEntryComment', 'MileageEntryCreatedBy', 'MileageEntryModifiedBy', 'MileageEntryUserName'], 'string'],
            [['MileageEntryMileageCardID', 'MileageEntryActivityID', 'MileageEntryActiveFlag'], 'integer'],
            [['MileageEntryMileageCardID'], 'exist', 'skipOnError' => true, 'targetClass' => MileageCard::className(), 'targetAttribute' => ['MileageEntryMileageCardID' => 'MileageCardID']],
        ];
    }

    /**
     * @inheritdoc
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageEntryMileageCard()
    {
        return $this->hasOne(MileageCard::className(), ['MileageCardID' => 'MileageEntryMileageCardID']);
    }
}
