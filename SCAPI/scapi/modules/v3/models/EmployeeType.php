<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "EmployeeTypeTb".
 *
 * @property integer $EmployeeTypeID
 * @property string $EmployeeTypeType
 * @property string $EmployeeTypeDescription
 * @property string $EmployeeTypeArchived
 * @property string $EmployeeTypeCreateDate
 * @property string $EmployeeTypeCreatedBy
 * @property string $EmployeeTypeModifiedDate
 * @property string $EmployeeTypeModifiedBy
 */

class EmployeeType extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'EmployeeTypeTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EmployeeTypeID'], 'required'],
            [['EmployeeTypeID'], 'integer'],
            [['EmployeeTypeType', 'EmployeeTypeDescription', 'EmployeeTypeArchived', 'EmployeeTypeCreateDate', 'EmployeeTypeCreatedBy',
				'EmployeeTypeModifiedDate', 'EmployeeTypeModifiedBy'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EmployeeTypeID' => 'Employee Type ID',
            'EmployeeTypeType' => 'Employee Type Type',
            'EmployeeTypeDescription' => 'Employee Type Description',
			'EmployeeTypeArchived' => 'Employee Type Archived',
			'EmployeeTypeCreateDate' => 'Employee Type Create Date',
			'EmployeeTypeCreatedBy' => 'Employee Type Created By',
			'EmployeeTypeModifiedDate' => 'Employee Type ModifiedDate',
			'EmployeeTypeModifiedBy' => 'Employee Type ModifiedBy',
        ];
    }
}
