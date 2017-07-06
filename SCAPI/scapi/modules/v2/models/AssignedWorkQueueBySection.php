<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAssignedWorkQueueBySection".
 *
 * @property string $MapGrid
 * @property string $SectionNumber
 * @property integer $AssignedWorkQueueCount
 * @property string $AssignedCount
 * @property string $UIDList
 * @property string $SearchString
 * @property integer $WorkQueueStatus
 */
class AssignedWorkQueueBySection extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAssignedWorkQueueBySection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid', 'SectionNumber', 'AssignedCount', 'UIDList', 'SearchString'], 'string'],
            [['AssignedWorkQueueCount', 'WorkQueueStatus'], 'integer'],
            [['AssignedCount'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGrid' => 'Map Grid',
            'SectionNumber' => 'Section Number',
            'AssignedWorkQueueCount' => 'Assigned Work Queue Count',
            'AssignedCount' => 'Assigned Count',
            'UIDList' => 'Uidlist',
            'SearchString' => 'Search String',
            'WorkQueueStatus' => 'Work Queue Status',
        ];
    }
}
