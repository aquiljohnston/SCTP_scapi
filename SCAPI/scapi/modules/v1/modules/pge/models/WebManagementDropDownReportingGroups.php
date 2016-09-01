<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownReportingGroups".
 *
 * @property string $ReportingGroupUID
 * @property string $GroupName
 */
class WebManagementDropDownReportingGroups extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownReportingGroups';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['GroupName', 'ReportingGroupUID'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'GroupName' => 'Group Name',
			'ReportingGroupUID' => 'Reporting Group UID',
        ];
    }
}
