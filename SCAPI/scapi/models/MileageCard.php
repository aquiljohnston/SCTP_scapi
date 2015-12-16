<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MileageCardTb".
 *
 * @property integer $MileageCardID
 * @property string $MileageCardEmpID
 * @property integer $MileageCardTechID
 * @property integer $MileageCardProjectID
 * @property string $MileageCardType
 * @property integer $MileageCardAppStatus
 * @property string $MileageCardCreateDate
 * @property string $MileageCardCreatedBy
 * @property string $MileageCardModifiedDate
 * @property string $MileageCardModifiedBy
 * @property integer $MileagCardBusinessMiles
 * @property integer $MileagCardPersonalMiles
 *
 * @property EmployeeTb $mileageCardEmp
 * @property MileageEntryTb[] $mileageEntryTbs
 */
class MileageCard extends \yii\db\ActiveRecord
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
            [['MileageCardEmpID', 'MileageCardTechID', 'MileageCardProjectID', 'MileageCardAppStatus', 'MileagCardBusinessMiles', 'MileagCardPersonalMiles'], 'integer'],
            [['MileageCardType', 'MileageCardCreatedBy', 'MileageCardModifiedBy'], 'string'],
            [['MileageCardCreateDate', 'MileageCardModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MileageCardID' => 'Mileage Card ID',
            'MileageCardEmpID' => 'Mileage Card Emp ID',
            'MileageCardTechID' => 'Mileage Card Tech ID',
            'MileageCardProjectID' => 'Mileage Card Project ID',
            'MileageCardType' => 'Mileage Card Type',
            'MileageCardAppStatus' => 'Mileage Card App Status',
            'MileageCardCreateDate' => 'Mileage Card Create Date',
            'MileageCardCreatedBy' => 'Mileage Card Created By',
            'MileageCardModifiedDate' => 'Mileage Card Modified Date',
            'MileageCardModifiedBy' => 'Mileage Card Modified By',
            'MileagCardBusinessMiles' => 'Mileag Card Business Miles',
            'MileagCardPersonalMiles' => 'Mileag Card Personal Miles',
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
