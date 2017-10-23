<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "rDropDown".
 *
 * @property integer $ID
 * @property integer $ProjectID
 * @property integer $CreatedUserID
 * @property integer $ModifiedUserID
 * @property string $CreatedDTLT
 * @property string $ModifiedDTLT
 * @property string $Comments
 * @property string $DropDownType
 * @property string $FilterName
 * @property integer $SortSeq
 * @property string $FieldDisplay
 * @property string $FieldValue
 * @property string $FieldDescription
 * @property string $ConversionValue
 */
class DropDown extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rDropDown';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ProjectID', 'CreatedUserID', 'ModifiedUserID', 'SortSeq'], 'integer'],
            [['CreatedDTLT', 'ModifiedDTLT'], 'safe'],
            [['Comments', 'DropDownType', 'FilterName', 'FieldDisplay', 'FieldValue', 'FieldDescription', 'ConversionValue'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ProjectID' => 'Project ID',
            'CreatedUserID' => 'Created User ID',
            'ModifiedUserID' => 'Modified User ID',
            'CreatedDTLT' => 'Created Dtlt',
            'ModifiedDTLT' => 'Modified Dtlt',
            'Comments' => 'Comments',
            'DropDownType' => 'Drop Down Type',
            'FilterName' => 'Filter Name',
            'SortSeq' => 'Sort Seq',
            'FieldDisplay' => 'Field Display',
            'FieldValue' => 'Field Value',
            'FieldDescription' => 'Field Description',
            'ConversionValue' => 'Conversion Value',
        ];
    }
}
