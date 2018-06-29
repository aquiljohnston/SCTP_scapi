<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "AllTimeCardsCurrentWeek_vw".
 *
 * @property integer $UserID
 * @property integer $TimeCardID
 * @property string $TimeCardApprovedBy
 * @property integer $TimeCardApproved
 * @property string $UserStatus
 * @property integer $TimeCardProjectID
 * @property string $UserFullName
 * @property string $TimeCardStartDate
 * @property string $TimeCardEndDate
 */
class AllTimeCardsCurrentWeek extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AllTimeCardsCurrentWeek_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID', 'TimeCardID', 'TimeCardApproved', 'UserStatus'], 'required'],
            [['UserID', 'TimeCardID', 'TimeCardApproved', 'TimeCardProjectID'], 'integer'],
            [['TimeCardApprovedBy', 'UserStatus', 'UserFullName', 'TimeCardStartDate', 'TimeCardEndDate'], 'string'],
        ];
    }

    /**
     * @inheritdoc
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
        ];
    }
}
