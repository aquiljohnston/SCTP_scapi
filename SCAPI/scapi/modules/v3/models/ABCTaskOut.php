<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "ABCTaskOut".
 *
 * @property int $ID
 * @property int $SCCEmployeeID
 * @property string $RefProjectID
 * @property string $ReportingTaskID
 * @property string $SrvDTLT
 * @property string $Value
 * @property string $Date
 * @property string $ABCTaskOutUID
 */
class ABCTaskOut extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ABCTaskOut';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['SCCEmployeeID'], 'integer'],
            [['RefProjectID', 'ReportingTaskID', 'Value', 'ABCTaskOutUID'], 'string'],
            [['SrvDTLT', 'Date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'SCCEmployeeID' => 'Sccemployee ID',
            'RefProjectID' => 'Ref Project ID',
            'ReportingTaskID' => 'Reporting Task ID',
            'SrvDTLT' => 'Srv Dtlt',
            'Value' => 'Value',
            'Date' => 'Date',
            'ABCTaskOutUID' => 'Abctask Out Uid',
        ];
    }
}
