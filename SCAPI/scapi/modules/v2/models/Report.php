<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "rReport".
 *
 * @property string $ID
 * @property integer $ProjectID
 * @property string $CreatedUserID
 * @property string $ModifiedUserID
 * @property string $CreateDTLT
 * @property string $ModifiedDTLT
 * @property string $InactiveDTLT
 * @property string $Comments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $ReportDisplayName
 * @property string $ReportDate
 * @property string $ReportInactiveDate
 * @property string $ReportType
 * @property string $ReportSPName
 * @property string $ReportDescription
 * @property integer $ReportSortSeq
 * @property string $Parm
 * @property integer $ParmInspectorFlag
 * @property integer $ParmDropDownFlag
 * @property integer $ParmDateOverrideFlag
 * @property integer $ParmBetweenDateFlag
 * @property integer $ParmDateFlag
 * @property integer $ExportFlag
 */
class Report extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rReport';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'ProjectID', 'CreatedUserID', 'ModifiedUserID', 'Revision', 'ActiveFlag'], 'required'],
            [['ID', 'CreatedUserID', 'ModifiedUserID', 'Comments', 'ReportDisplayName', 'ReportType', 'ReportSPName', 'ReportDescription', 'Parm'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'ReportSortSeq', 'ParmInspectorFlag', 'ParmDropDownFlag', 'ParmDateOverrideFlag', 'ParmBetweenDateFlag', 'ParmDateFlag', 'ExportFlag'], 'integer'],
            [['CreateDTLT', 'ModifiedDTLT', 'InactiveDTLT', 'ReportDate', 'ReportInactiveDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'Report ID',
            'ProjectID' => 'Project ID',
            'CreatedUserID' => 'Created User Uid',
            'ModifiedUserID' => 'Modified User Uid',
            'CreateDTLT' => 'Create Dtlt',
            'ModifiedDTLT' => 'Modified Dtlt',
            'InactiveDTLT' => 'Inactive Dtlt',
            'Comments' => 'Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'ReportDisplayName' => 'Report Display Name',
            'ReportDate' => 'Report Date',
            'ReportInactiveDate' => 'Report Inactive Date',
            'ReportType' => 'Report Type',
            'ReportSPName' => 'Report Spname',
            'ReportDescription' => 'Report Description',
            'ReportSortSeq' => 'Report Sort Seq',
            'Parm' => 'Parm',
            'ParmInspectorFlag' => 'Parm Inspector Flag',
            'ParmDropDownFlag' => 'Parm Drop Down Flag',
            'ParmDateOverrideFlag' => 'Parm Date Override Flag',
            'ParmBetweenDateFlag' => 'Parm Between Date Flag',
            'ParmDateFlag' => 'Parm Date Flag',
            'ExportFlag' => 'Export Flag',
        ];
    }
}
