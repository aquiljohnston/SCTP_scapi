<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "tWorkQueue".
 *
 * @property integer $ID
 * @property integer $WorkOrderID
 * @property integer $AssignedUserID
 * @property integer $WorkQueueStatus
 * @property string $SectionNumber
 * @property integer $CreatedBy
 * @property string $CreatedDate
 * @property integer $ModifiedBy
 * @property string $ModifiedDate
 * @property integer $tAssetID
 * @property integer $TaskedOut
 * @property string $ScheduledDispatchDate
 *
 * @property UserTb $createdBy
 * @property UserTb $modifiedBy
 * @property UserTb $assignedUser
 * @property RStatusLookup $workQueueStatus
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
            [['WorkOrderID', 'AssignedUserID', 'WorkQueueStatus', 'CreatedBy', 'ModifiedBy', 'tAssetID', 'TaskedOut'], 'integer'],
            [['SectionNumber'], 'string'],
            [['AssignedUserID'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['AssignedUserID' => 'UserID']],
            [['CreatedBy'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedBy' => 'UserID']],
            [['ModifiedBy'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['ModifiedBy' => 'UserID']],
            [['CreatedDate', 'ModifiedDate', 'ScheduledDispatchDate'], 'safe'],
            [['WorkQueueStatus'], 'exist', 'skipOnError' => true, 'targetClass' => StatusLookup::className(), 'targetAttribute' => ['WorkQueueStatus' => 'StatusCode']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'WorkOrderID' => 'Work Order ID',
            'AssignedUserID' => 'Assigned User ID',
            'WorkQueueStatus' => 'Work Queue Status',
            'SectionNumber' => 'Section Number',
            'CreatedBy' => 'Created By',
            'CreatedDate' => 'Created Date',
            'ModifiedBy' => 'Modified By',
            'ModifiedDate' => 'Modified Date',
            'tAssetID' => 'T Asset ID',
            'TaskedOut' => 'Tasked Out',
            'ScheduledDispatchDate' => 'Scheduled Dispatch Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignedUser()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'AssignedUserID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'CreatedBy']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifiedBy()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'ModifiedBy']);
    }
}
