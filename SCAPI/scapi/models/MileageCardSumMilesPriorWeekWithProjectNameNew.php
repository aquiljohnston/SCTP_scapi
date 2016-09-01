<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MileageCardSumMilesPriorWeekWithProjectNameNew_vw".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $MileageStartDate
 * @property string $MileageEndDate
 * @property integer $ProjectID
 * @property integer $MileageCardProjectID
 * @property string $ProjectName
 * @property integer $MileageCardTechID
 * @property string $SumMiles
 * @property string $MileageCardApprovedFlag
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardSupervisorName
 * @property integer $COUNTS
 */
class MileageCardSumMilesPriorWeekWithProjectNameNew extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MileageCardSumMilesPriorWeekWithProjectNameNew_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID', 'ProjectID', 'ProjectName'], 'required'],
            [['UserID', 'ProjectID', 'MileageCardProjectID', 'MileageCardTechID', 'COUNTS'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'ProjectName', 'MileageCardApprovedFlag', 'MileageCardApprovedBy', 'MileageCardSupervisorName'], 'string'],
            [['MileageStartDate', 'MileageEndDate'], 'safe'],
            [['SumMiles'], 'number']
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
            'MileageStartDate' => 'Mileage Start Date',
            'MileageEndDate' => 'Mileage End Date',
            'ProjectID' => 'Project ID',
            'MileageCardProjectID' => 'Mileage Card Project ID',
            'ProjectName' => 'Project Name',
            'MileageCardTechID' => 'Mileage Card Tech ID',
            'SumMiles' => 'Sum Miles',
            'MileageCardApprovedFlag' => 'Mileage Card Approved Flag',
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardSupervisorName' => 'Mileage Card Supervisor Name',
            'COUNTS' => 'Counts',
        ];
    }
}
