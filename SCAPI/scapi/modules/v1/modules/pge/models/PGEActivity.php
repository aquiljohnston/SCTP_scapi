<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "ActivityTb".
 *
 * @property integer $ActivityID
 * @property string $ActivityUID
 * @property integer $ActivityProjectID
 * @property string $ActivitySourceID
 * @property string $ActivityCreatedUserUID
 * @property string $ActivityModifiedUserUID
 * @property string $ActivitySrvDTLT
 * @property string $ActivitySrvDTLTOffset
 * @property string $ActivitySrcDTLT
 * @property string $ActivityGPSType
 * @property string $ActivityGPSSentence
 * @property double $ActivityLatitude
 * @property double $ActivityLongitude
 * @property string $ActivityShape
 * @property string $ActivityComments
 * @property string $ActivityType
 * @property double $ActivityBatteryLevel
 * @property string $ActivityStartTime
 * @property string $ActivityStopTime
 * @property integer $ActivityElapsedSec
 * @property string $ActivityTitle
 * @property string $ActivityBillingCode
 * @property string $ActivityCode
 * @property string $ActivityPayCode
 * @property integer $ActivityArchiveFlag
 * @property string $ActivityCreateDate
 * @property string $ActivityModifiedDate
 * @property string $ActivityRevisionComments
 */
class PGEActivity extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ActivityTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ActivityUID', 'ActivitySourceID', 'ActivityCreatedUserUID', 'ActivityModifiedUserUID', 'ActivityGPSType', 'ActivityGPSSentence', 'ActivityShape', 'ActivityComments', 'ActivityType', 'ActivityTitle', 'ActivityBillingCode', 'ActivityCode', 'ActivityPayCode', 'ActivityRevisionComments'], 'string'],
            [['ActivityProjectID', 'ActivityElapsedSec', 'ActivityArchiveFlag'], 'integer'],
            [['ActivitySrvDTLT', 'ActivitySrvDTLTOffset', 'ActivitySrcDTLT', 'ActivityStartTime', 'ActivityStopTime', 'ActivityCreateDate', 'ActivityModifiedDate'], 'safe'],
            [['ActivityLatitude', 'ActivityLongitude', 'ActivityBatteryLevel'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ActivityID' => 'Activity ID',
            'ActivityUID' => 'Activity Uid',
            'ActivityProjectID' => 'Activity Project ID',
            'ActivitySourceID' => 'Activity Source ID',
            'ActivityCreatedUserUID' => 'Activity Created User Uid',
            'ActivityModifiedUserUID' => 'Activity Modified User Uid',
            'ActivitySrvDTLT' => 'Activity Srv Dtlt',
            'ActivitySrvDTLTOffset' => 'Activity Srv Dtltoffset',
            'ActivitySrcDTLT' => 'Activity Src Dtlt',
            'ActivityGPSType' => 'Activity Gpstype',
            'ActivityGPSSentence' => 'Activity Gpssentence',
            'ActivityLatitude' => 'Activity Latitude',
            'ActivityLongitude' => 'Activity Longitude',
            'ActivityShape' => 'Activity Shape',
            'ActivityComments' => 'Activity Comments',
            'ActivityType' => 'Activity Type',
            'ActivityBatteryLevel' => 'Activity Battery Level',
            'ActivityStartTime' => 'Activity Start Time',
            'ActivityStopTime' => 'Activity Stop Time',
            'ActivityElapsedSec' => 'Activity Elapsed Sec',
            'ActivityTitle' => 'Activity Title',
            'ActivityBillingCode' => 'Activity Billing Code',
            'ActivityCode' => 'Activity Code',
            'ActivityPayCode' => 'Activity Pay Code',
            'ActivityArchiveFlag' => 'Activity Archive Flag',
            'ActivityCreateDate' => 'Activity Create Date',
            'ActivityModifiedDate' => 'Activity Modified Date',
            'ActivityRevisionComments' => 'Activity Revision Comments',
        ];
    }
}
