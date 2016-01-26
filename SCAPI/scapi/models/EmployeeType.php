<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EmployeeTypeTb".
 *
 * @property integer $EmployeeTypeID
 * @property string $EmployeeType
 * @property string $EmployeeTypeDescription
 */
class EmployeeType extends \yii\db\ActiveRecord
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
            [['EmployeeType', 'EmployeeTypeDescription'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EmployeeTypeID' => 'Employee Type ID',
            'EmployeeType' => 'Employee Type',
            'EmployeeTypeDescription' => 'Employee Type Description',
        ];
    }
}
