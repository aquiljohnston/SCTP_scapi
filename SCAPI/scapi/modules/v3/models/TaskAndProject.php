<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "vTaskAndProject".
 *
 * @property integer $TaskID
 * @property string $TaskName
 * @property integer $ProjectID
 * @property integer $GPSInterval
 * @property string $TaskQBReferenceID
 */
class TaskAndProject extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTaskAndProject';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['TaskID'], 'required'],
            [['TaskID', 'ProjectID', 'GPSInterval'], 'integer'],
            [['TaskName', 'TaskQBReferenceID'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'TaskID' => 'Task ID',
            'TaskName' => 'Task Name',
            'ProjectID' => 'Project ID',
            'GPSInterval' => 'Gpsinterval',
            'TaskQBReferenceID' => 'Task Qbreference ID',
        ];
    }
}
