<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "MileageCardSumMilesCurrentWeekWithProjectName_vw".
 *
 * @property int $MileageCardID
 * @property int $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $MileageStartDate
 * @property string $MileageEndDate
 * @property int $ProjectID
 * @property int $MileageCardProjectID
 * @property string $ProjectName
 * @property int $MileageCardTechID
 * @property string $SumMiles
 * @property string $MileageCardApprovedFlag
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardSupervisorName
 * @property int $Count
 * @property string $UserFullName
 */
class MileageCardSumMilesCurrentWeekWithProjectName extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MileageCardSumMilesCurrentWeekWithProjectName_vw';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['MileageCardID', 'UserID', 'ProjectID', 'MileageCardProjectID', 'MileageCardTechID', 'Count'], 'integer'],
            [['UserID', 'ProjectID', 'ProjectName', 'SumMiles', 'Count'], 'required'],
            [['UserName', 'UserFirstName', 'UserLastName', 'ProjectName', 'MileageCardApprovedFlag', 'MileageCardApprovedBy', 'MileageCardSupervisorName', 'UserFullName'], 'string'],
            [['MileageStartDate', 'MileageEndDate'], 'safe'],
            [['SumMiles'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'MileageCardID' => 'Mileage Card ID',
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
            'Count' => 'Count',
            'UserFullName' => 'User Full Name',
        ];
    }
}
