<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "tWorkQueue".
 *
 * @property integer $tWorkQueueID
 * @property string $CreatedBy
 * @property string $ModifiedBy
 * @property string $CreatedDateTime
 * @property string $ModifiedDateTime
 * @property string $ClientWorkOrderID
 * @property string $AssignedUserID
 * @property integer $WorkQueueStatus
 * @property string $SectionNumber
 * @property string $tAssetID
 */
class WorkQueue extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tWorkQueue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CreatedBy', 'ModifiedBy', 'ClientWorkOrderID', 'AssignedUserID', 'SectionNumber', 'tAssetID'], 'string'],
            [['CreatedDateTime', 'ModifiedDateTime'], 'safe'],
            [['WorkQueueStatus'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tWorkQueueID' => 'T Work Queue ID',
            'CreatedBy' => 'Create By',
            'ModifiedBy' => 'Modified By',
            'CreatedDateTime' => 'Created Date Time',
            'ModifiedDateTime' => 'Modified Date Time',
            'ClientWorkOrderID' => 'Client Work Order ID',
            'AssignedUserID' => 'Assigned User ID',
            'WorkQueueStatus' => 'Work Queue Status',
            'SectionNumber' => 'Section Number',
            'tAssetID' => 'T Asset ID',
        ];
    }
}
