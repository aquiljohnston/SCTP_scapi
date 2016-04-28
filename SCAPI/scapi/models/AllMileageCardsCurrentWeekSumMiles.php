<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "AllMileageCardsCurrentWeekSumMiles_vw".
 *
 * @property integer $MileageCardID
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property integer $MileageCardProjectID
 * @property string $ProjectName
 * @property string $SumBusinessMiles
 * @property string $SumPersonalMiles
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardApproved
 * @property string $MileageStartDate
 * @property string $MileageEndDate
 * @property string $UserStatus
 */
class AllMileageCardsCurrentWeekSumMiles extends \yii\db\ActiveRecord
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
            [['MileageCardID', 'UserID', 'ProjectName', 'UserStatus'], 'required'],
            [['MileageCardID', 'UserID', 'MileageCardProjectID'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'ProjectName', 'MileageCardApprovedBy', 'MileageCardApproved', 'UserStatus'], 'string'],
            [['SumBusinessMiles', 'SumPersonalMiles'], 'number'],
            [['MileageStartDate', 'MileageEndDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MileageCardID' => 'Mileage Card ID',
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'MileageCardProjectID' => 'Mileage Card Project ID',
            'ProjectName' => 'Project Name',
            'SumBusinessMiles' => 'Sum Business Miles',
            'SumPersonalMiles' => 'Sum Personal Miles',
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardApproved' => 'Mileage Card Approved',
            'MileageStartDate' => 'Mileage Start Date',
            'MileageEndDate' => 'Mileage End Date',
            'UserStatus' => 'User Status',
        ];
    }
}
