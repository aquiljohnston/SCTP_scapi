<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "AllMileageCardsCurrentWeek_vw".
 *
 * @property int $UserID
 * @property int $MileageCardID
 * @property string $MileageCardApprovedBy
 * @property int $MileageCardApproved
 * @property string $UserStatus
 * @property int $MileageCardProjectID
 * @property string $UserFullName
 * @property string $MileageCardStartDate
 * @property string $MileageCardEndDate
 * @property double $Sun
 * @property double $Mon
 * @property double $Tue
 * @property double $Wed
 * @property double $Thu
 * @property double $Fri
 * @property double $Sat
 * @property double $WeeklyTotal
 * @property double $MileageCardBusinessMiles
 * @property double $MileageCardPersonalMiles
 * @property double $MileageCardAllMileage_calc
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
            [['UserID', 'MileageCardID', 'MileageCardApproved', 'UserStatus', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'WeeklyTotal', 'MileageCardBusinessMiles', 'MileageCardPersonalMiles', 'MileageCardAllMileage_calc'], 'required'],
            [['UserID', 'MileageCardID', 'MileageCardApproved', 'MileageCardProjectID'], 'integer'],
            [['MileageCardApprovedBy', 'UserStatus', 'UserFullName', 'MileageCardStartDate', 'MileageCardEndDate'], 'string'],
            [['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'WeeklyTotal', 'MileageCardBusinessMiles', 'MileageCardPersonalMiles', 'MileageCardAllMileage_calc'], 'number'],
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
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardApproved' => 'Mileage Card Approved',
            'UserStatus' => 'User Status',
            'MileageCardProjectID' => 'Mileage Card Project ID',
            'UserFullName' => 'User Full Name',
            'MileageCardStartDate' => 'Mileage Card Start Date',
            'MileageCardEndDate' => 'Mileage Card End Date',
            'Sun' => 'Sun',
            'Mon' => 'Mon',
            'Tue' => 'Tue',
            'Wed' => 'Wed',
            'Thu' => 'Thu',
            'Fri' => 'Fri',
            'Sat' => 'Sat',
            'WeeklyTotal' => 'Weekly Total',
            'MileageCardBusinessMiles' => 'Mileage Card Business Miles',
            'MileageCardPersonalMiles' => 'Mileage Card Personal Miles',
            'MileageCardAllMileage_calc' => 'Mileage Card All Mileage Calc',
        ];
    }
}
