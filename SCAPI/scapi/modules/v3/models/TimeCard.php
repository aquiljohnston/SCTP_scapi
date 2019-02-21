<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "TimeCardTb".
 *
 * @property integer $TimeCardID
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property integer $TimeCardProjectID
 * @property string $TimeCardProjectGroupID
 * @property integer $TimeCardTechID
 * @property integer $TimeCardActiveFlag
 * @property integer $TimeCardApprovedFlag
 * @property string $TimeCardApprovedBy
 * @property string $TimeCardSupervisorName
 * @property string $TimeCardComment
 * @property integer $TimeCardArchiveFlag
 * @property string $TimeCardCreateDate
 * @property string $TimeCardCreatedBy
 * @property string $TimeCardModifiedDate
 * @property string $TimeCardModifiedBy
 * @property string $TimeCardSubmittedOasis
 * @property string $TimeCardSubmttedQuickBooks
 * @property string $TimeCardSubmttedADP
 * @property integer $TimeCardPMApprovedFlag
 *
 * @property TimeEntryTb[] $timeEntryTbs
 */
class TimeCard extends \app\modules\v3\models\BaseActiveRecord
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
            [['TimeCardStartDate', 'TimeCardEndDate', 'TimeCardCreateDate', 'TimeCardModifiedDate', 'TimeCardSubmittedOasis', 'TimeCardSubmttedQuickBooks', 'TimeCardSubmttedADP'], 'safe'],
            [['TimeCardProjectID', 'TimeCardTechID', 'TimeCardActiveFlag', 'TimeCardApprovedFlag', 'TimeCardArchiveFlag', 'TimeCardPMApprovedFlag'], 'integer'],
            [['TimeCardProjectGroupID', 'TimeCardApprovedBy', 'TimeCardSupervisorName', 'TimeCardComment', 'TimeCardCreatedBy', 'TimeCardModifiedBy'], 'string'],
            [['TimeCardProjectID', 'TimeCardStartDate', 'TimeCardTechID'], 'unique', 'targetAttribute' => ['TimeCardProjectID', 'TimeCardStartDate', 'TimeCardTechID'], 'message' => 'The combination of Time Card Start Date, Time Card Project ID and Time Card Tech ID has already been taken.'],
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
            'TimeCardActiveFlag' => 'Time Card Active Flag',
            'TimeCardApprovedFlag' => 'Time Card Approved Flag',
            'TimeCardApprovedBy' => 'Time Card Approved By',
            'TimeCardSupervisorName' => 'Time Card Supervisor Name',
            'TimeCardComment' => 'Time Card Comment',
            'TimeCardArchiveFlag' => 'Time Card Archive Flag',
            'TimeCardCreateDate' => 'Time Card Create Date',
            'TimeCardCreatedBy' => 'Time Card Created By',
            'TimeCardModifiedDate' => 'Time Card Modified Date',
            'TimeCardModifiedBy' => 'Time Card Modified By',
            'TimeCardSubmittedOasis' => 'Time Card Submitted Oasis',
            'TimeCardSubmttedQuickBooks' => 'Time Card Submtted Quick Books',
            'TimeCardSubmttedADP' => 'Time Card Submtted Adp',
            'TimeCardPMApprovedFlag' => 'Time Card Pmapproved Flag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeEntryTbs()
    {
        return $this->hasMany(TimeEntryTb::className(), ['TimeEntryTimeCardID' => 'TimeCardID']);
    }
}
