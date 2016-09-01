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
 * @property string $Notification ID
 * @property string $Surveyor
 * @property string $Employee Type
 * @property string $Compliance Date
 * @property string $Status
 * @property string $Dispatch Method
 * @property string $IR UID
 * @property string $Assigned Work Queue UID
 * @property string $UserUID
 * @property string $ComplianceYearMonth
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
            [['Division', 'WorkCenter', 'SurveyType', 'MapPlat', 'Notification ID', 'Surveyor', 'Employee Type', 'Status', 'Dispatch Method', 'IR UID', 'Assigned Work Queue UID', 'UserUID', 'ComplianceYearMonth'], 'string'],
            [['Compliance Date'], 'safe'],
            [['IR UID'], 'required']
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
            'Notification ID' => 'Notification  ID',
            'Surveyor' => 'Surveyor',
            'Employee Type' => 'Employee  Type',
            'Compliance Date' => 'Compliance  Date',
            'Status' => 'Status',
            'Dispatch Method' => 'Dispatch  Method',
            'IR UID' => 'Ir  Uid',
            'Assigned Work Queue UID' => 'Assigned  Work  Queue  Uid',
            'UserUID' => 'User Uid',
            'ComplianceYearMonth' => 'Compliance Year Month',
        ];
    }
}
