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
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('pgeDevDb');
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
