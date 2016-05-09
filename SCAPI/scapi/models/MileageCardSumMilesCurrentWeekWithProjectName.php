<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MileageCardSumMilesCurrentWeekWithProjectName_vw".
 *
 * @property integer $MileageCardID
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property integer $MileageCardProjectID
 * @property string $ProjectName
 * @property string $SumMiles
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardApprovedFlag
 * @property string $MileageStartDate
 * @property string $MileageEndDate
 * @property string $MileageCardSupervisorName
 */
class MileageCardSumMilesCurrentWeekWithProjectName extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MileageCardSumMilesCurrentWeekWithProjectName_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MileageCardID', 'UserID', 'ProjectName'], 'required'],
            [['ProjectID', 'UserID', 'MileageCardProjectID', 'MileageCardTechID'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'ProjectName', 'MileageCardApprovedBy', 'MileageCardApprovedFlag', 'MileageCardSupervisorName'], 'string'],
            [['SumMiles'], 'number'],
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
            'SumMiles' => 'Sum Miles',
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardApprovedFlag' => 'Mileage Card Approved Flag',
            'MileageStartDate' => 'Mileage Start Date',
            'MileageEndDate' => 'Mileage End Date',
			'MileageCardSupervisorName' => 'MileageCardSupervisorName',
        ];
    }
}
