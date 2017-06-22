<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "TimeCardSumHoursWorkedCurrentWeekWithProjectName_vw".
 *
 * @property integer $TimeCardID
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property integer $ProjectID
 * @property integer $TimeCardProjectID
 * @property integer $TimeCardTechID
 * @property string $SumHours
 * @property integer $RemainderMinutes
 * @property integer $Sums
 * @property string $TimeCardApprovedFlag
 * @property string $TimeCardApprovedBy
 * @property string $TimeCardSupervisorName
 */
class TimeCardSumHoursWorkedCurrentWeekWithProjectName extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TimeCardSumHoursWorkedCurrentWeekWithProjectName_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['TimeCardID', 'UserID', 'TimeCardProjectID', 'TimeCardTechID', 'RemainderMinutes', 'Sums', 'ProjectID'], 'integer'],
            [['UserID'], 'required'],
            [['UserName', 'UserFirstName', 'UserLastName', 'TimeCardApprovedFlag', 'TimeCardApprovedBy', 'TimeCardSupervisorName'], 'string'],
            [['TimeCardStartDate', 'TimeCardEndDate'], 'safe'],
            [['SumHours'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'TimeCardID' => 'Time Card ID',
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'TimeCardStartDate' => 'Time Card Start Date',
            'TimeCardEndDate' => 'Time Card End Date',
			'ProjectID' => 'Project ID',
            'TimeCardProjectID' => 'Time Card Project ID',
            'TimeCardTechID' => 'Time Card Tech ID',
            'SumHours' => 'Sum Hours',
            'RemainderMinutes' => 'Remainder Minutes',
            'Sums' => 'Sums',
            'TimeCardApprovedFlag' => 'Time Card Approved Flag',
            'TimeCardApprovedBy' => 'Time Card Approved By',
            'TimeCardSupervisorName' => 'Time Card Supervisor Name',
        ];
    }
}
