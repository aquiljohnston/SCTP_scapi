<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MileageEntryTb".
 *
 * @property string $MileageEntryID
 * @property string $MileageEntryStartingMileage
 * @property string $MileageEntryEndingMileage
 * @property string $MileageEntryStartDate
 * @property string $MileageEntryEndDate
 * @property string $MileageEntryDate
 * @property string $MileageEntryType
 * @property integer $MileageEntryMileageCardID
 * @property integer $MileageEntryActivityID
 * @property string $MileageEntryApprovedBy
 * @property integer $MileageEntryStatus
 * @property string $MileageEntryComment
 * @property string $MileageEntryCreateDate
 * @property string $MileageEntryCreatedBy
 * @property string $MileageEntryModifiedDate
 * @property string $MileageEntryModifiedBy
 *
 * @property ActivityTb $mileageEntryActivity
 * @property MileageCardTb $mileageEntryMileageCard
 */
class MileageEntry extends \yii\db\ActiveRecord
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
            [['MileageEntryType', 'MileageEntryMileageCardID', 'MileageEntryActivityID', 'MileageEntryStatus'], 'integer'],
            [['MileageEntryApprovedBy', 'MileageEntryComment', 'MileageEntryCreatedBy', 'MileageEntryModifiedBy'], 'string'],
            [['MileageEntryDate', 'MileageEntryStartDate', 'MileageEntryEndDate',  'MileageEntryCreateDate', 'MileageEntryModifiedDate'], 'safe']
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
			'MileageEntryDate' => 'Mileage Entry Date',
			'MileageEntryType' => 'Mileage Entry Type',
            'MileageEntryMileageCardID' => 'Mileage Entry Mileage Card ID',
            'MileageEntryActivityID' => 'Mileage Entry Activity ID',
            'MileageEntryApprovedBy' => 'Mileage Entry Approved By',
            'MileageEntryStatus' => 'Mileage Entry Status',
            'MileageEntryComment' => 'Mileage Entry Comment',
            'MileageEntryCreateDate' => 'Mileage Entry Create Date',
            'MileageEntryCreatedBy' => 'Mileage Entry Created By',
            'MileageEntryModifiedDate' => 'Mileage Entry Modified Date',
            'MileageEntryModifiedBy' => 'Mileage Entry Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageEntryActivity()
    {
        return $this->hasOne(ActivityTb::className(), ['ActivtyID' => 'MileageEntryActivityID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageEntryMileageCard()
    {
        return $this->hasOne(MileageCardTb::className(), ['MileageCardID' => 'MileageEntryMileageCardID']);
    }
}
