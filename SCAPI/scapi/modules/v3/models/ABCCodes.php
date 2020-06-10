<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "refABCCodes".
 *
 * @property int $ID
 * @property int $ABCCodeID
 * @property int|null $ProjectID
 * @property string|null $TaskID
 * @property int|null $IsActive
 * @property int|null $IsSource
 * @property string|null $ABCCodeLabel
 * @property string|null $ABCCodeDescription
 * @property int|null $ResultTypeID
 * @property string $ReportingTaskID
 * @property string|null $CreatedDate
 * @property string|null $LastRefreshDate
 * @property int|null $MinThreshold
 * @property int|null $MaxThreshold
 */
class ABCCodes extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'refABCCodes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ABCCodeID', 'ReportingTaskID'], 'required'],
            [['ABCCodeID', 'ProjectID', 'IsActive', 'IsSource', 'ResultTypeID', 'MinThreshold', 'MaxThreshold'], 'integer'],
            [['TaskID', 'ABCCodeLabel', 'ABCCodeDescription', 'ReportingTaskID'], 'string'],
            [['CreatedDate', 'LastRefreshDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ABCCodeID' => 'Abccode ID',
            'ProjectID' => 'Project ID',
            'TaskID' => 'Task ID',
            'IsActive' => 'Is Active',
            'IsSource' => 'Is Source',
            'ABCCodeLabel' => 'Abccode Label',
            'ABCCodeDescription' => 'Abccode Description',
            'ResultTypeID' => 'Result Type ID',
            'ReportingTaskID' => 'Reporting Task ID',
            'CreatedDate' => 'Created Date',
            'LastRefreshDate' => 'Last Refresh Date',
            'MinThreshold' => 'Min Threshold',
            'MaxThreshold' => 'Max Threshold',
        ];
    }
}
