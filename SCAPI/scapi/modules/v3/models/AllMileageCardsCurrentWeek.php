<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "AllMileageCardsCurrentWeek_vw".
 *
 * @property int $UserID
 * @property int $MileageCardID
 * @property int $MileageCardBusinessMiles
 * @property int $MileageCardPersonalMiles
 * @property int $MileageCardAllMileage_calc
 * @property string $MileageCardApprovedBy
 * @property int $MileageCardApproved
 * @property string $UserStatus
 * @property int $MileageCardProjectID
 * @property string $MileageCardStartDate
 * @property string $MileageCardEndDate
 */
class AllMileageCardsCurrentWeek extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AllMileageCardsCurrentWeek_vw';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UserID', 'MileageCardID', 'UserStatus'], 'required'],
            [['UserID', 'MileageCardID', 'MileageCardBusinessMiles', 'MileageCardPersonalMiles', 'MileageCardAllMileage_calc', 'MileageCardApproved', 'MileageCardProjectID'], 'integer'],
            [['MileageCardApprovedBy', 'UserStatus', 'MileageCardStartDate', 'MileageCardEndDate'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'MileageCardID' => 'Mileage Card ID',
            'MileageCardBusinessMiles' => 'Mileage Card Business Miles',
            'MileageCardPersonalMiles' => 'Mileage Card Personal Miles',
            'MileageCardAllMileage_calc' => 'Mileage Card All Mileage Calc',
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardApproved' => 'Mileage Card Approved',
            'UserStatus' => 'User Status',
            'MileageCardProjectID' => 'Mileage Card Project ID',
            'MileageCardStartDate' => 'Mileage Card Start Date',
            'MileageCardEndDate' => 'Mileage Card End Date',
        ];
    }
}
