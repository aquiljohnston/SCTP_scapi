<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vTaskAndProject".
 *
 * @property int $TaskID
 * @property string $TaskName
 * @property int $ProjectID
 * @property int $GPSInterval
 * @property string $TaskQBReferenceID
 * @property string $Category
 * @property string $TaskReferenceID
 */
class TaskAndProject extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vTaskAndProject';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TaskID'], 'required'],
            [['TaskID', 'ProjectID', 'GPSInterval'], 'integer'],
            [['TaskName', 'TaskQBReferenceID', 'Category', 'TaskReferenceID'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'TaskID' => 'Task ID',
            'TaskName' => 'Task Name',
            'ProjectID' => 'Project ID',
            'GPSInterval' => 'Gpsinterval',
            'TaskQBReferenceID' => 'Task Qbreference ID',
            'Category' => 'Category',
            'TaskReferenceID' => 'Task Reference ID',
        ];
    }
}
