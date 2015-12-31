<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TimeCardTb".
 *
 * @property string $TimeCardID
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property string $TimeCardHoursWorked
 * @property string $TimeCardProjectID
 * @property string $TimeCardTechID
 * @property integer $TimeCardApproved
 * @property string $TimeCardSupervisorName
 * @property string $TimeCardComment
 * @property string $TimeCardCreateDate
 * @property string $TimeCardCreatedBy
 * @property string $TimeCardModifiedDate
 * @property string $TimeCardModifiedBy
 *
 * @property EmployeeTb $timeCardTech
 * @property ProjectTb $timeCardProject
 * @property TimeEntryTb[] $timeEntryTbs
 */
class TimeCard extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TimeCardTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['TimeCardStartDate', 'TimeCardEndDate', 'TimeCardCreateDate', 'TimeCardModifiedDate'], 'safe'],
            [['TimeCardHoursWorked'], 'number'],
            [['TimeCardProjectID', 'TimeCardTechID', 'TimeCardApproved'], 'integer'],
            [['TimeCardSupervisorName', 'TimeCardComment', 'TimeCardCreatedBy', 'TimeCardModifiedBy'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'TimeCardID' => 'Time Card ID',
            'TimeCardStartDate' => 'Time Card Start Date',
            'TimeCardEndDate' => 'Time Card End Date',
            'TimeCardHoursWorked' => 'Time Card Hours Worked',
            'TimeCardProjectID' => 'Time Card Project ID',
            'TimeCardTechID' => 'Time Card Tech ID',
            'TimeCardApproved' => 'Time Card Approved',
            'TimeCardSupervisorName' => 'Time Card Supervisor Name',
            'TimeCardComment' => 'Time Card Comment',
            'TimeCardCreateDate' => 'Time Card Create Date',
            'TimeCardCreatedBy' => 'Time Card Created By',
            'TimeCardModifiedDate' => 'Time Card Modified Date',
            'TimeCardModifiedBy' => 'Time Card Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeCardTech()
    {
        return $this->hasOne(EmployeeTb::className(), ['EmployeeID' => 'TimeCardTechID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeCardProject()
    {
        return $this->hasOne(ProjectTb::className(), ['ProjectID' => 'TimeCardProjectID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeEntryTbs()
    {
        return $this->hasMany(TimeEntryTb::className(), ['TimeEntryTimeCardID' => 'TimeCardID']);
    }
}
