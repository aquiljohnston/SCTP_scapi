<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "xReportingGroupEmployeexRef".
 *
 * @property integer $ReportingGroupEmployeeID
 * @property string $UserUID
 * @property string $ReportingGroupUID
 * @property string $RoleUID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $CreateDatetime
 * @property string $ModifiedDatetime
 * @property integer $Revision
 * @property integer $ActiveFlag
 */
class ReportingGroupEmployeeRef extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xReportingGroupEmployeexRef';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserUID', 'ReportingGroupUID', 'RoleUID', 'CreatedUserUID', 'ModifiedUserUID'], 'string'],
            [['CreateDatetime', 'ModifiedDatetime'], 'safe'],
            [['Revision', 'ActiveFlag'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ReportingGroupEmployeeID' => 'Reporting Group Employee ID',
            'UserUID' => 'User Uid',
            'ReportingGroupUID' => 'Reporting Group Uid',
            'RoleUID' => 'Role Uid',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'CreateDatetime' => 'Create Datetime',
            'ModifiedDatetime' => 'Modified Datetime',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
        ];
    }
}
