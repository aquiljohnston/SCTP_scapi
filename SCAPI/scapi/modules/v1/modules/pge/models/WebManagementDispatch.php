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
 * @property string $Map/Plat
 * @property string $Notification ID
 * @property string $ComplianceDueDate
 * @property string $SAP Released
 * @property integer $Assigned
 * @property string $ComplianceYearMonth
 * @property string $FLOC
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
            [['Division', 'WorkCenter', 'SurveyType', 'Map/Plat', 'Notification ID', 'ComplianceYearMonth', 'InspectionRequestUID', 'FLOC'], 'string'],
            [['ComplianceDueDate', 'SAP Released'], 'safe'],
            [['Assigned'], 'required'],
            [['Assigned'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
			'InspectionRequestUID' => 'Inspection Request UID',
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
            'SurveyType' => 'Survey Type',
            'Map/Plat' => 'Map/ Plat',
            'Notification ID' => 'Notification  ID',
            'ComplianceDueDate' => 'Compliance Due Date',
            'SAP Released' => 'Sap  Released',
            'Assigned' => 'Assigned',
            'ComplianceYearMonth' => 'Compliance Year Month',
            'FLOC' => 'Floc',
        ];
    }
}
