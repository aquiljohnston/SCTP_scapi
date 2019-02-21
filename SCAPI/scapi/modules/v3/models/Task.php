<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "TaskTb".
 *
 * @property integer $TaskID
 * @property string $TaskName
 * @property string $TaskReferenceID
 * @property string $TaskQBReferenceID
 * @property string $TaskParentReferenceID
 * @property string $TaskRefreshDateTime
 */
class Task extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'refTask';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['TaskName', 'TaskReferenceID', 'TaskQBReferenceID', 'TaskParentReferenceID'], 'string'],
            [['TaskRefreshDateTime'], 'safe'],
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
            'TaskReferenceID' => 'Task Reference ID',
            'TaskQBReferenceID' => 'Task Qbreference ID',
            'TaskParentReferenceID' => 'Task Parent Reference ID',
            'TaskRefreshDateTime' => 'Task Refresh Date Time',
        ];
    }
}