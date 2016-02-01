<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "AllUnapprovedTimeCardsCurrentWeek_vw".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property integer $TimeCardID
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property string $TimeCardHoursWorked
 * @property string $TimeCardApprovedBy
 * @property string $TimeCardApproved
 * @property string $UserStatus
 */
class AllUnapprovedTimeCardsCurrentWeek extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AllUnapprovedTimeCardsCurrentWeek_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID', 'TimeCardID', 'UserStatus'], 'required'],
            [['UserID', 'TimeCardID'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'TimeCardApprovedBy', 'TimeCardApproved', 'UserStatus'], 'string'],
            [['TimeCardStartDate', 'TimeCardEndDate'], 'safe'],
            [['TimeCardHoursWorked'], 'number']
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
            'TimeCardID' => 'Time Card ID',
            'TimeCardStartDate' => 'Time Card Start Date',
            'TimeCardEndDate' => 'Time Card End Date',
            'TimeCardHoursWorked' => 'Time Card Hours Worked',
            'TimeCardApprovedBy' => 'Time Card Approved By',
            'TimeCardApproved' => 'Time Card Approved',
            'UserStatus' => 'User Status',
        ];
    }
}
