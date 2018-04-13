<?php
/**
 * Created by PhpStorm.
 * User: tzhang
 * Date: 12/26/2017
 * Time: 4:07 PM
 */

namespace app\modules\v2\models;

/**
 * This is the model class for table "vTaskAndProject".
 *
 * @property integer $TaskID
 * @property string $FilterName
 * @property integer $ProjectID
 */
class TaskAndProject extends BaseActiveRecord
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
            [['TaskID', 'ProjectID'], 'integer'],
            [['FilterName'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'TaskID' => 'Task ID',
            'FilterName' => 'Filter Name',
            'ProjectID' => 'Project ID'
        ];
    }
}