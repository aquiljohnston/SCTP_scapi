<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "AllApprovedMileageCardsCurrentWeekSumMiles_vw".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property integer $MileageCardID
 * @property string $MileageStartDate
 * @property string $MileageEndDate
 * @property integer $SumBusinessMiles
 * @property integer $SumPersonalMiles
 * @property integer $SUMMileageCardAllMileage_calc
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardApproved
 * @property string $UserStatus
 * @property integer $MileageCardProjectID
 */
class AllMileageCardsCurrentWeekSumMiles extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AllMileageCardsCurrentWeekSumMiles_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID', 'MileageCardID', 'UserStatus'], 'required'],
            [['UserID', 'MileageCardID', 'SumBusinessMiles', 'SumPersonalMiles', 'SUMMileageCardAllMileage_calc', 'MileageCardProjectID'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'MileageCardApprovedBy', 'MileageCardApproved', 'UserStatus'], 'string'],
            [['MileageStartDate', 'MileageEndDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'MileageCardID' => 'Mileage Card ID',
            'MileageStartDate' => 'Mileage Start Date',
            'MileageEndDate' => 'Mileage End Date',
            'SumBusinessMiles' => 'Sum Business Miles',
            'SumPersonalMiles' => 'Sum Personal Miles',
            'SUMMileageCardAllMileage_calc' => 'Summileage Card All Mileage Calc',
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardApproved' => 'Mileage Card Approved',
            'UserStatus' => 'User Status',
            'MileageCardProjectID' => 'Mileage Card Project ID',
        ];
    }
}
