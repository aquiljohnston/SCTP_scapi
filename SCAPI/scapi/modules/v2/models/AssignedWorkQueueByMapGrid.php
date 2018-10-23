<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAssignedWorkQueueByMapGrid2".
 *
 * @property string $MapGrid
 * @property string $AssignedUser
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property int $InspectionAttemptcounter
 * @property int $SectionFlag
 * @property int $AssignedWorkOrderCount
 * @property int $InProgressFlag
 * @property string $AssignedCount
 * @property string $Percent Completed
 * @property int $Total
 * @property int $Remaining
 * @property string $BillingCode
 * @property string $InspectionType
 * @property string $OfficeName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $UserName
 * @property string $ComplianceStartDate
 * @property string $CompliancenEndDate
 */
class AssignedWorkQueueByMapGrid extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vAssignedWorkQueueByMapGrid2';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['MapGrid', 'AssignedUser', 'AssignedCount', 'BillingCode', 'InspectionType', 'OfficeName', 'UserFirstName', 'UserLastName', 'UserName'], 'string'],
            [['ComplianceStart', 'ComplianceEnd', 'ComplianceStartDate', 'CompliancenEndDate'], 'safe'],
            [['InspectionAttemptcounter', 'SectionFlag', 'AssignedWorkOrderCount', 'InProgressFlag', 'Total', 'Remaining'], 'integer'],
            [['SectionFlag', 'InProgressFlag', 'AssignedCount'], 'required'],
            [['Percent Completed'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'MapGrid' => 'Map Grid',
            'AssignedUser' => 'Assigned User',
            'ComplianceStart' => 'Compliance Start',
            'ComplianceEnd' => 'Compliance End',
            'InspectionAttemptcounter' => 'Inspection Attemptcounter',
            'SectionFlag' => 'Section Flag',
            'AssignedWorkOrderCount' => 'Assigned Work Order Count',
            'InProgressFlag' => 'In Progress Flag',
            'AssignedCount' => 'Assigned Count',
            'Percent Completed' => 'Percent  Completed',
            'Total' => 'Total',
            'Remaining' => 'Remaining',
            'BillingCode' => 'Billing Code',
            'InspectionType' => 'Inspection Type',
            'OfficeName' => 'Office Name',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'UserName' => 'User Name',
            'ComplianceStartDate' => 'Compliance Start Date',
            'CompliancenEndDate' => 'Compliancen End Date',
        ];
    }
}
