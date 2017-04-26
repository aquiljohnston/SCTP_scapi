<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vDropDowns".
 *
 * @property integer $DropDownID
 * @property string $DropDownUID
 * @property integer $ProjectID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $CreateDTLT
 * @property string $ModifiedDTLT
 * @property string $InactiveDTLT
 * @property string $Comments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $DropDownType
 * @property string $FilterName
 * @property integer $SortSeq
 * @property string $FieldDisplay
 * @property string $FieldDescription
 * @property string $FieldType
 * @property string $FieldValue
 * @property integer $DefaultFlag
 * @property string $DefaultType
 * @property string $DefaultValue
 * @property integer $BreadcrumbFreq
 * @property integer $SurveyModeFlag
 * @property string $AdditionalStrData1
 * @property integer $AdditionalIntData1
 * @property string $AdditionalStrData2
 * @property integer $AdditionalIntData2
 * @property integer $OutValueNeededFlag
 * @property integer $SpecialFlag
 * @property string $OutValue
 * @property string $InValue
 * @property string $AltValue
 * @property string $StartDate
 * @property string $InactiveDate
 */
class DropDowns extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vDropDowns';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['DropDownUID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'DropDownType', 'FilterName', 'FieldDisplay', 'FieldDescription', 'FieldType', 'FieldValue', 'DefaultType', 'DefaultValue', 'AdditionalStrData1', 'AdditionalStrData2', 'OutValue', 'InValue', 'AltValue'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'SortSeq', 'DefaultFlag', 'BreadcrumbFreq', 'SurveyModeFlag', 'AdditionalIntData1', 'AdditionalIntData2', 'OutValueNeededFlag', 'SpecialFlag'], 'integer'],
            [['CreateDTLT', 'ModifiedDTLT', 'InactiveDTLT', 'StartDate', 'InactiveDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'DropDownID' => 'Drop Down ID',
            'DropDownUID' => 'Drop Down Uid',
            'ProjectID' => 'Project ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'CreateDTLT' => 'Create Dtlt',
            'ModifiedDTLT' => 'Modified Dtlt',
            'InactiveDTLT' => 'Inactive Dtlt',
            'Comments' => 'Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'DropDownType' => 'Drop Down Type',
            'FilterName' => 'Filter Name',
            'SortSeq' => 'Sort Seq',
            'FieldDisplay' => 'Field Display',
            'FieldDescription' => 'Field Description',
            'FieldType' => 'Field Type',
            'FieldValue' => 'Field Value',
            'DefaultFlag' => 'Default Flag',
            'DefaultType' => 'Default Type',
            'DefaultValue' => 'Default Value',
            'BreadcrumbFreq' => 'Breadcrumb Freq',
            'SurveyModeFlag' => 'Survey Mode Flag',
            'AdditionalStrData1' => 'Additional Str Data1',
            'AdditionalIntData1' => 'Additional Int Data1',
            'AdditionalStrData2' => 'Additional Str Data2',
            'AdditionalIntData2' => 'Additional Int Data2',
            'OutValueNeededFlag' => 'Out Value Needed Flag',
            'SpecialFlag' => 'Special Flag',
            'OutValue' => 'Out Value',
            'InValue' => 'In Value',
            'AltValue' => 'Alt Value',
            'StartDate' => 'Start Date',
            'InactiveDate' => 'Inactive Date',
        ];
    }
}
