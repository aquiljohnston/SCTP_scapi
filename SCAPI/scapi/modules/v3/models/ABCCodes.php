<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "refABCCodes".
 *
 * @property int $ID
 * @property int $ABCCodeID
 * @property int $ProjectID
 * @property string $TaskID
 * @property int $IsActive
 * @property int $IsSource
 * @property string $ABCCodeLabel
 * @property string $ABCCodeDescription
 * @property int $ResultTypeID
 * @property string $ReportingTaskID
 * @property string $CreatedDate
 * @property string $LastRefreshDate
 *
 * @property TTaskOut[] $tTaskOuts
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
            [['ABCCodeID', 'ProjectID', 'IsActive', 'IsSource', 'ResultTypeID'], 'integer'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTTaskOuts()
    {
        return $this->hasMany(TaskOut::className(), ['ABCCodeID' => 'ABCCodeID']);
    }
}
