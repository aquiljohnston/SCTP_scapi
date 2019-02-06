<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "MileageCardTb".
 *
 * @property int $MileageCardID
 * @property int $MileageCardTechID
 * @property int $MileageCardProjectID
 * @property string $MileageCardProjectGroupID
 * @property string $MileageStartDate
 * @property string $MileageEndDate
 * @property int $MileageCardBusinessMiles
 * @property int $MileageCardPersonalMiles
 * @property int $MileageCardActiveFlag
 * @property int $MileageCardApprovedFlag
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardSupervisorName
 * @property int $MileageCardArchiveFlag
 * @property string $MileageCardCreateDate
 * @property string $MileageCardCreatedBy
 * @property string $MileageCardModifiedDate
 * @property string $MileageCardModifiedBy
 * @property string $MileageCardComment
 * @property string $MileageCardSubmittedOasis
 * @property string $MileageCardSubmittedQuickbooks
 * @property string $MileageCardSubmittedADP
 * @property int $MileageCardPMApprovedFlag
 *
 * @property MileageEntryTb[] $mileageEntryTbs
 */
class MileageCard extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MileageCardTb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['MileageCardTechID', 'MileageCardProjectID', 'MileageCardBusinessMiles', 'MileageCardPersonalMiles', 'MileageCardActiveFlag', 'MileageCardApprovedFlag', 'MileageCardArchiveFlag', 'MileageCardPMApprovedFlag'], 'integer'],
            [['MileageCardProjectGroupID', 'MileageCardApprovedBy', 'MileageCardSupervisorName', 'MileageCardCreatedBy', 'MileageCardModifiedBy', 'MileageCardComment'], 'string'],
            [['MileageStartDate', 'MileageEndDate', 'MileageCardCreateDate', 'MileageCardModifiedDate', 'MileageCardSubmittedOasis', 'MileageCardSubmittedQuickbooks', 'MileageCardSubmittedADP'], 'safe'],
            [['MileageCardProjectID', 'MileageCardTechID', 'MileageStartDate'], 'unique', 'targetAttribute' => ['MileageCardProjectID', 'MileageCardTechID', 'MileageStartDate']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'MileageCardID' => 'Mileage Card ID',
            'MileageCardTechID' => 'Mileage Card Tech ID',
            'MileageCardProjectID' => 'Mileage Card Project ID',
            'MileageCardProjectGroupID' => 'Mileage Card Project Group ID',
            'MileageStartDate' => 'Mileage Start Date',
            'MileageEndDate' => 'Mileage End Date',
            'MileageCardBusinessMiles' => 'Mileage Card Business Miles',
            'MileageCardPersonalMiles' => 'Mileage Card Personal Miles',
            'MileageCardActiveFlag' => 'Mileage Card Active Flag',
            'MileageCardApprovedFlag' => 'Mileage Card Approved Flag',
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardSupervisorName' => 'Mileage Card Supervisor Name',
            'MileageCardArchiveFlag' => 'Mileage Card Archive Flag',
            'MileageCardCreateDate' => 'Mileage Card Create Date',
            'MileageCardCreatedBy' => 'Mileage Card Created By',
            'MileageCardModifiedDate' => 'Mileage Card Modified Date',
            'MileageCardModifiedBy' => 'Mileage Card Modified By',
            'MileageCardComment' => 'Mileage Card Comment',
            'MileageCardSubmittedOasis' => 'Mileage Card Submitted Oasis',
            'MileageCardSubmittedQuickbooks' => 'Mileage Card Submitted Quickbooks',
            'MileageCardSubmittedADP' => 'Mileage Card Submitted Adp',
            'MileageCardPMApprovedFlag' => 'Mileage Card Pmapproved Flag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageCardEmp()
    {
        return $this->hasOne(EmployeeTb::className(), ['EmployeeID' => 'MileageCardEmpID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMileageEntryTbs()
    {
        return $this->hasMany(MileageEntryTb::className(), ['MileageEntryMileageCardID' => 'MileageCardID']);
    }
}
