<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementAssignedWorkQueue".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyType
 * @property string $MapPlat
 * @property string $NotificationID
 * @property string $Surveyor
 * @property string $EmployeeType
 * @property string $ComplianceDate
 * @property string $Status
 * @property string $DispatchMethod
 * @property string $IRUID
 * @property string $AssignedWorkQueueUID
 * @property string $UserUID
 * @property string $MapGridUID
 * @property string $ComplianceYearMonth
 * @property string $FLOC
 * @property string $AssignedDate
 */
class WebManagementAssignedWorkQueue extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementAssignedWorkQueue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Division', 'WorkCenter', 'SurveyType', 'MapPlat', 'NotificationID', 'Surveyor', 'EmployeeType', 'Status', 'DispatchMethod', 'IRUID', 'AssignedWorkQueueUID', 'UserUID', 'MapGridUID', 'ComplianceYearMonth', 'FLOC'], 'string'],
            [['ComplianceDate', 'AssignedDate'], 'safe'],
            [['IRUID'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
            'SurveyType' => 'Survey Type',
            'MapPlat' => 'Map Plat',
            'NotificationID' => 'Notification ID',
            'Surveyor' => 'Surveyor',
            'EmployeeType' => 'Employee Type',
            'ComplianceDate' => 'Compliance Date',
            'Status' => 'Status',
            'DispatchMethod' => 'Dispatch Method',
            'IRUID' => 'Iruid',
            'AssignedWorkQueueUID' => 'Assigned Work Queue Uid',
            'UserUID' => 'User Uid',
            'MapGridUID' => 'Map Grid Uid',
            'ComplianceYearMonth' => 'Compliance Year Month',
            'FLOC' => 'Floc',
			'AssignedDate' => 'AssignedDate',
        ];
    }
}
