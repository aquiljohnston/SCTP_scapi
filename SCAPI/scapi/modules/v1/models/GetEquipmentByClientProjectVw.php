<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "GetEquipmentByClientProject_vw".
 *
 * @property integer $EquipmentID
 * @property string $Name
 * @property string $Serial Number
 * @property string $Details
 * @property string $Type
 * @property string $Client Name
 * @property string $Project Name
 * @property string $Accepted Flag
 * @property integer $ProjectID
 */
class GetEquipmentByClientProjectVw extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'GetEquipmentByClientProject_vw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EquipmentID'], 'required'],
            [['EquipmentID', 'ProjectID'], 'integer'],
            [['Name', 'Serial Number', 'Details', 'Type', 'Client Name', 'Project Name', 'Accepted Flag'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EquipmentID' => 'Equipment ID',
            'Name' => 'Name',
            'Serial Number' => 'Serial  Number',
            'Details' => 'Details',
            'Type' => 'Type',
            'Client Name' => 'Client  Name',
            'Project Name' => 'Project  Name',
            'Accepted Flag' => 'Accepted  Flag',
			'ProjectID' => 'Project ID',
        ];
    }
}
