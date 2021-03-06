<?php

namespace app\modules\v2\modules\scana\models;

use Yii;
use app\modules\v2\models\BaseUser;

/**
 * This is the model class for table "tTaskOut".
 *
 * @property int $ID
 * @property int $ActivityID
 * @property int $AboveGroundLeakCount
 * @property int $BelowGroundLeakCount
 * @property int $ServicesCount
 * @property int $FeetOfMain
 * @property int $CreatedUserID
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffSet
 * @property string $MapGrid
 * @property string $StartDTLT
 * @property string $EndDTLT
 * @property int $DeletedFlag
 * @property string $Comments
 * @property double $TotalMapTime
 * @property int $FeetOfTransmission
 * @property int $FeetOfHighPressure
 * @property string $TaskOutUID
 *
 * @property UserTb $createdUser
 */
class TaskOut extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tTaskOut';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ActivityID', 'CreatedUserID'], 'required'],
            [['ActivityID', 'AboveGroundLeakCount', 'BelowGroundLeakCount', 'ServicesCount', 'FeetOfMain', 'CreatedUserID', 'DeletedFlag', 'FeetOfTransmission', 'FeetOfHighPressure'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffSet', 'StartDTLT', 'EndDTLT'], 'safe'],
            [['MapGrid', 'Comments', 'TaskOutUID'], 'string'],
            [['TotalMapTime'], 'number'],
            [['CreatedUserID'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedUserID' => 'UserID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ActivityID' => 'Activity ID',
            'AboveGroundLeakCount' => 'Above Ground Leak Count',
            'BelowGroundLeakCount' => 'Below Ground Leak Count',
            'ServicesCount' => 'Services Count',
            'FeetOfMain' => 'Feet Of Main',
            'CreatedUserID' => 'Created User ID',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffSet' => 'Srv Dtltoff Set',
            'MapGrid' => 'Map Grid',
            'StartDTLT' => 'Start Dtlt',
            'EndDTLT' => 'End Dtlt',
            'DeletedFlag' => 'Deleted Flag',
            'Comments' => 'Comments',
            'TotalMapTime' => 'Total Map Time',
            'FeetOfTransmission' => 'Feet Of Transmission',
            'FeetOfHighPressure' => 'Feet Of High Pressure',
            'TaskOutUID' => 'Task Out Uid',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'CreatedUserID']);
    }
}
