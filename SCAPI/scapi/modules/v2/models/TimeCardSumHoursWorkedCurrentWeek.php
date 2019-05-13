<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vTimeCardSumHoursWorkedCurrentWeek".
 *
 * @property int $TimeCardID
 * @property int $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property int $ProjectID
 * @property int $TimeCardProjectID
 * @property string $ProjectName
 * @property int $TimeCardTechID
 * @property string $SumHours
 * @property string $RemainderMinutes
 * @property int $Sums
 * @property int $TimeCardApprovedFlag
 * @property string $TimeCardApprovedBy
 * @property string $TimeCardSupervisorName
 * @property string $UserFullName
 * @property string $TimeCardDates
 * @property string $SubmittedOasis
 * @property string $SubmittedQuickBoods
 */
class TimeCardSumHoursWorkedCurrentWeek extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vTimeCardSumHoursWorkedCurrentWeek';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TimeCardID', 'UserID', 'ProjectID', 'ProjectName', 'Sums', 'TimeCardApprovedBy', 'SubmittedOasis', 'SubmittedQuickBoods'], 'required'],
            [['TimeCardID', 'UserID', 'ProjectID', 'TimeCardProjectID', 'TimeCardTechID', 'Sums', 'TimeCardApprovedFlag'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'ProjectName', 'SumHours', 'RemainderMinutes', 'TimeCardApprovedBy', 'TimeCardSupervisorName', 'UserFullName', 'TimeCardDates', 'SubmittedOasis', 'SubmittedQuickBoods'], 'string'],
            [['TimeCardStartDate', 'TimeCardEndDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
            'ProjectName' => 'Project Name',
            'TimeCardTechID' => 'Time Card Tech ID',
            'SumHours' => 'Sum Hours',
            'RemainderMinutes' => 'Remainder Minutes',
            'Sums' => 'Sums',
            'TimeCardApprovedFlag' => 'Time Card Approved Flag',
            'TimeCardApprovedBy' => 'Time Card Approved By',
            'TimeCardSupervisorName' => 'Time Card Supervisor Name',
            'UserFullName' => 'User Full Name',
            'TimeCardDates' => 'Time Card Dates',
            'SubmittedOasis' => 'Submitted Oasis',
            'SubmittedQuickBoods' => 'Submitted Quick Boods',
        ];
    }
}
