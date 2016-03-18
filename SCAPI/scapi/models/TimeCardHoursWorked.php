<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TimeCardHoursWorked_vw".
 *
 * @property string $Row
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property integer $TimeCardID
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property integer $TimeCardProjectID
 * @property integer $TimeCardTechID
 * @property integer $TimeCardHoursWorked
 * @property string $TimeCardApprovedFlag
 * @property string $TimeCardApprovedBy
 * @property string $TimeCardSupervisorName
 */
class TimeCardHoursWorked extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TimeCardHoursWorked_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Row', 'UserID', 'TimeCardID', 'TimeCardProjectID', 'TimeCardTechID', 'TimeCardHoursWorked'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'TimeCardApprovedFlag', 'TimeCardApprovedBy', 'TimeCardSupervisorName'], 'string'],
            [['TimeCardStartDate', 'TimeCardEndDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Row' => 'Row',
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'TimeCardID' => 'Time Card ID',
            'TimeCardStartDate' => 'Time Card Start Date',
            'TimeCardEndDate' => 'Time Card End Date',
            'TimeCardProjectID' => 'Time Card Project ID',
            'TimeCardTechID' => 'Time Card Tech ID',
            'TimeCardHoursWorked' => 'Time Card Hours Worked',
            'TimeCardApprovedFlag' => 'Time Card Approved Flag',
            'TimeCardApprovedBy' => 'Time Card Approved By',
            'TimeCardSupervisorName' => 'Time Card Supervisor Name',
        ];
    }
}
