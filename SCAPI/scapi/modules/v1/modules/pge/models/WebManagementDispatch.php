<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDispatch".
 *
 * @property string $InspectionRequestUID
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyType
 * @property string $MapPlat
 * @property string $Notification ID
 * @property string $ComplianceDueDate
 * @property string $SAP Released
 * @property integer $Assigned
 * @property string $ComplianceYearMonth
 * @property string $FLOC
 * @property integer $Within3Days
 * @property string $PreviousServices
 */
class WebManagementDispatch extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDispatch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['InspectionRequestUID', 'Assigned', 'Within3Days'], 'required'],
            [['InspectionRequestUID', 'Division', 'WorkCenter', 'SurveyType', 'MapPlat', 'Notification ID', 'ComplianceYearMonth', 'FLOC'], 'string'],
            [['ComplianceDueDate', 'SAP Released'], 'safe'],
            [['Assigned', 'Within3Days', 'PreviousServices'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'InspectionRequestUID' => 'Inspection Request Uid',
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
            'SurveyType' => 'Survey Type',
            'MapPlat' => 'Map Plat',
            'Notification ID' => 'Notification  ID',
            'ComplianceDueDate' => 'Compliance Due Date',
            'SAP Released' => 'Sap  Released',
            'Assigned' => 'Assigned',
            'ComplianceYearMonth' => 'Compliance Year Month',
            'FLOC' => 'Floc',
            'Within3Days' => 'Within3 Days',
            'PreviousServices' => 'Previous Services',
        ];
    }
}
