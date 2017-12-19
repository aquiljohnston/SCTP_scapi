<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "MileageCardTb".
 *
 * @property integer $MileageCardID
 * @property integer $MileageCardTechID
 * @property integer $MileageCardProjectID
 * @property string $MileageCardProjectGroupID
 * @property string $MileageStartDate
 * @property string $MileageEndDate
 * @property integer $MileageCardBusinessMiles
 * @property integer $MileageCardPersonalMiles
 * @property string $MileageCardApprovedFlag
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardSupervisorName
 * @property string $MileageCardArchiveFlag
 * @property string $MileageCardActiveFlag
 * @property string $MileageCardCreateDate
 * @property string $MileageCardCreatedBy
 * @property string $MileageCardModifiedDate
 * @property string $MileageCardModifiedBy

 *
 * @property EmployeeTb $mileageCardEmp
 * @property MileageEntryTb[] $mileageEntryTbs
 */
class MileageCard extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MileageCardTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MileageCardTechID', 'MileageCardProjectID', 'MileageCardBusinessMiles', 'MileageCardPersonalMiles'], 'integer'],
            [['MileageCardSupervisorName', 'MileageCardApprovedBy', 'MileageCardCreatedBy', 'MileageCardModifiedBy', 'MileageCardApprovedFlag', 'MileageCardProjectGroupID', 'MileageCardArchiveFlag', 'MileageCardActiveFlag'], 'string'],
            [['MileageStartDate', 'MileageEndDate', 'MileageCardCreateDate', 'MileageCardModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MileageCardID' => 'Mileage Card ID',
            'MileageCardTechID' => 'Mileage Card Tech ID',
            'MileageCardProjectID' => 'Mileage Card Project ID',
			'MileageCardProjectGroupID' => 'Mileage Card Project Group ID',
			'MileageStartDate' => 'Mileage Start Date',
			'MileageEndDate' => 'Mileage End Date',
			'MileageCardBusinessMiles' => 'Mileage Card Business Miles',
            'MileageCardPersonalMiles' => 'Mileage Card Personal Miles',
            'MileageCardApprovedFlag' => 'Mileage Card Approved Flag',
			'MileageCardApprovedBy' => 'Mileage Card Approved By',
			'MileageCardSupervisorName' => 'Mileage Card Supervisor Name',
			'MileageCardActiveFlag' => 'Mileage Card Active Flag',
			'MileageCardArchiveFlag' => 'Mileage Card Archive Flag',
            'MileageCardCreateDate' => 'Mileage Card Create Date',
            'MileageCardCreatedBy' => 'Mileage Card Created By',
            'MileageCardModifiedDate' => 'Mileage Card Modified Date',
            'MileageCardModifiedBy' => 'Mileage Card Modified By',

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageCardEmp()
    {
        return $this->hasOne(EmployeeTb::className(), ['EmployeeID' => 'MileageCardEmpID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageEntryTbs()
    {
        return $this->hasMany(MileageEntryTb::className(), ['MileageEntryMileageCardID' => 'MileageCardID']);
    }
}
