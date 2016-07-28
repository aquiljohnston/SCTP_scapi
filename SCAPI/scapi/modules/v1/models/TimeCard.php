<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "TimeCardTb".
 *
 * @property integer $TimeCardID
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property integer $TimeCardProjectID
 * @property string $TimeCardProjectGroupID
 * @property string $TimeCardTechID
 * @property string $TimeCardActiveFlag
 * @property string $TimeCardApprovedFlag
 * @property integer $TimeCardApprovedBy
 * @property string $TimeCardSupervisorName
 * @property string $TimeCardComment
 * @property string $TimeCardArchiveFlag
 * @property string $TimeCardCreateDate
 * @property string $TimeCardCreatedBy
 * @property string $TimeCardModifiedDate
 * @property string $TimeCardModifiedBy
 *
 * @property EmployeeTb $timeCardTech
 * @property ProjectTb $timeCardProject
 * @property TimeEntryTb[] $timeEntryTbs
 */
class TimeCard extends BaseActiveRecord
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
            [['TimeCardProjectID', 'TimeCardTechID', 'TimeCardApprovedBy'], 'integer'],
            [['TimeCardSupervisorName', 'TimeCardComment', 'TimeCardCreatedBy', 'TimeCardModifiedBy', 'TimeCardApprovedFlag', 'TimeCardProjectGroupID',
				'TimeCardActiveFlag', 'TimeCardArchiveFlag' ], 'string']
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
            'TimeCardProjectID' => 'Time Card Project ID',
			'TimeCardProjectGroupID' => 'Time Card Project Group ID',
            'TimeCardTechID' => 'Time Card Tech ID',
			'TimeCardActiveFlag' => 'TimeCardActiveFlag',
            'TimeCardApprovedFlag' => 'Time Card Approved Flag',
			'TimeCardApprovedBy' => 'Time Card Approved By',
            'TimeCardSupervisorName' => 'Time Card Supervisor Name',
            'TimeCardComment' => 'Time Card Comment',
			'TimeCardArchiveFlag' => 'TimeCardArchiveFlag',
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
