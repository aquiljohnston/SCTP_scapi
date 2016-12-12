<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "rReportingGroup".
 *
 * @property integer $rReportingGroupID
 * @property string $ReportingGroupUID
 * @property integer $ProjectID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $CreatedDateTime
 * @property string $ModifiedDateTime
 * @property string $GroupName
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property integer $IsGroupFlag
 */
class ReportingGroup extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rReportingGroup';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ReportingGroupUID', 'CreatedUserUID', 'ModifiedUserUID', 'GroupName'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'IsGroupFlag'], 'integer'],
            [['CreatedDateTime', 'ModifiedDateTime'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rReportingGroupID' => 'R Reporting Group ID',
            'ReportingGroupUID' => 'Reporting Group Uid',
            'ProjectID' => 'Project ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'CreatedDateTime' => 'Created Date Time',
            'ModifiedDateTime' => 'Modified Date Time',
            'GroupName' => 'Group Name',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'IsGroupFlag' => 'Is Group Flag',
        ];
    }
}
