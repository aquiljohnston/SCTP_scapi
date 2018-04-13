<?php
/**
 * Created by PhpStorm.
 * User: tzhang
 * Date: 12/19/2017
 * Time: 1:17 PM
 */

namespace app\modules\v2\models;

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
class Task extends \app\modules\v2\models\BaseActiveRecord
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