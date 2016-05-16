<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "AllApprovedMileageCardsCurrentWeek_vw".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property integer $MileageCardID
 * @property string $MileageStartDate
 * @property string $MileageEndtDate
 * @property integer $MileageCardBusinessMiles
 * @property integer $MileageCardPersonalMiles
 * @property integer $MileageCardAllMileage_calc
 * @property string $MileageCardApprovedBy
 * @property string $MileageCardApproved
 * @property string $UserStatus
 * @property integer $MileageCardProjectID
 */
class AllApprovedMileageCardsCurrentWeek extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AllApprovedMileageCardsCurrentWeek_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID', 'MileageCardID', 'UserStatus'], 'required'],
            [['UserID', 'MileageCardID', 'MileageCardBusinessMiles', 'MileageCardPersonalMiles', 'MileageCardAllMileage_calc', 'MileageCardProjectID'], 'integer'],
            [['UserName', 'UserFirstName', 'UserLastName', 'MileageCardApprovedBy', 'MileageCardApproved', 'UserStatus'], 'string'],
            [['MileageStartDate', 'MileageEndtDate'], 'safe']
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
            'MileageCardID' => 'Mileage Card ID',
            'MileageStartDate' => 'Mileage Start Date',
            'MileageEndtDate' => 'Mileage Endt Date',
            'MileageCardBusinessMiles' => 'Mileage Card Business Miles',
            'MileageCardPersonalMiles' => 'Mileage Card Personal Miles',
            'MileageCardAllMileage_calc' => 'Mileage Card All Mileage Calc',
            'MileageCardApprovedBy' => 'Mileage Card Approved By',
            'MileageCardApproved' => 'Mileage Card Approved',
            'UserStatus' => 'User Status',
			'MileageCardProjectID' => 'Mileage Card Project ID',
        ];
    }
}
