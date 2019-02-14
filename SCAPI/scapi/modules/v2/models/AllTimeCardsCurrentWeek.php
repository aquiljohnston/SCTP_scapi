<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "AllTimeCardsCurrentWeek_vw".
 *
 * @property int $UserID
 * @property int $TimeCardID
 * @property string $TimeCardApprovedBy
 * @property int $TimeCardApproved
 * @property string $UserStatus
 * @property int $TimeCardProjectID
 * @property string $UserFullName
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 * @property string $Sun
 * @property string $Mon
 * @property string $Tue
 * @property string $Wed
 * @property string $Thu
 * @property string $Fri
 * @property string $Sat
 * @property string $WeeklyTotal
 */
class AllTimeCardsCurrentWeek extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AllTimeCardsCurrentWeek_vw';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UserID', 'TimeCardID', 'TimeCardApproved', 'UserStatus'], 'required'],
            [['UserID', 'TimeCardID', 'TimeCardApproved', 'TimeCardProjectID'], 'integer'],
            [['TimeCardApprovedBy', 'UserStatus', 'UserFullName', 'TimeCardStartDate', 'TimeCardEndDate', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'WeeklyTotal'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'TimeCardID' => 'Time Card ID',
            'TimeCardApprovedBy' => 'Time Card Approved By',
            'TimeCardApproved' => 'Time Card Approved',
            'UserStatus' => 'User Status',
            'TimeCardProjectID' => 'Time Card Project ID',
            'UserFullName' => 'User Full Name',
            'TimeCardStartDate' => 'Time Card Start Date',
            'TimeCardEndDate' => 'Time Card End Date',
            'Sun' => 'Sun',
            'Mon' => 'Mon',
            'Tue' => 'Tue',
            'Wed' => 'Wed',
            'Thu' => 'Thu',
            'Fri' => 'Fri',
            'Sat' => 'Sat',
            'WeeklyTotal' => 'Weekly Total',
        ];
    }
}
