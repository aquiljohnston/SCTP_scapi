<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "tWorkOrder".
 *
 * @property integer $ID
 * @property string $ClientWorkOrderID
 * @property integer $CreatedBy
 * @property integer $ModifiedBy
 * @property string $CreatedDateTime
 * @property string $ModifiedDateTime
 * @property string $InspectionType
 * @property string $HouseNumber
 * @property string $Street
 * @property string $AptSuite
 * @property string $City
 * @property string $State
 * @property string $Zip
 * @property string $MeterNumber
 * @property string $MeterLocationDesc
 * @property string $LocationType
 * @property double $LocationLatitude
 * @property double $LocationLongitude
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property double $MapLatitudeBegin
 * @property double $MapLongitudeBegin
 * @property double $MapLatitudeEnd
 * @property double $MapLongitudeEnd
 * @property string $AccountNumber
 * @property string $AccountName
 * @property string $AccountTelephoneNumber
 * @property string $Comments
 * @property integer $CompletedFlag
 * @property string $CompletedDate
 * @property integer $InspectionAttemptCounter
 * @property string $SequenceNumber
 * @property string $SectionNumber
 * @property string $Shape
 *
 * @property UserTb $createdBy
 * @property UserTb $modifiedBy
 */
class WorkOrder extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tWorkOrder';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ClientWorkOrderID', 'InspectionType', 'HouseNumber', 'Street', 'AptSuite', 'City', 'State', 'Zip', 'MeterNumber', 'MeterLocationDesc', 'LocationType', 'MapGrid', 'AccountNumber', 'AccountName', 'AccountTelephoneNumber', 'Comments', 'SequenceNumber', 'SectionNumber', 'Shape'], 'string'],
            [['CreatedBy', 'ModifiedBy', 'CompletedFlag', 'InspectionAttemptCounter'], 'integer'],
            [['CreatedDateTime', 'ModifiedDateTime', 'ComplianceStart', 'ComplianceEnd', 'CompletedDate'], 'safe'],
            [['LocationLatitude', 'LocationLongitude', 'MapLatitudeBegin', 'MapLongitudeBegin', 'MapLatitudeEnd', 'MapLongitudeEnd'], 'number'],
            [['CreatedBy'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedBy' => 'UserID']],
            [['ModifiedBy'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['ModifiedBy' => 'UserID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ClientWorkOrderID' => 'Client Work Order ID',
            'CreatedBy' => 'Created By',
            'ModifiedBy' => 'Modified By',
            'CreatedDateTime' => 'Created Date Time',
            'ModifiedDateTime' => 'Modified Date Time',
            'InspectionType' => 'Inspection Type',
            'HouseNumber' => 'House Number',
            'Street' => 'Street',
            'AptSuite' => 'Apt Suite',
            'City' => 'City',
            'State' => 'State',
            'Zip' => 'Zip',
            'MeterNumber' => 'Meter Number',
            'MeterLocationDesc' => 'Meter Location Desc',
            'LocationType' => 'Location Type',
            'LocationLatitude' => 'Location Latitude',
            'LocationLongitude' => 'Location Longitude',
            'MapGrid' => 'Map Grid',
            'ComplianceStart' => 'Compliance Start',
            'ComplianceEnd' => 'Compliance End',
            'MapLatitudeBegin' => 'Map Latitude Begin',
            'MapLongitudeBegin' => 'Map Longitude Begin',
            'MapLatitudeEnd' => 'Map Latitude End',
            'MapLongitudeEnd' => 'Map Longitude End',
            'AccountNumber' => 'Account Number',
            'AccountName' => 'Account Name',
            'AccountTelephoneNumber' => 'Account Telephone Number',
            'Comments' => 'Comments',
            'CompletedFlag' => 'Completed Flag',
            'CompletedDate' => 'Completed Date',
            'InspectionAttemptCounter' => 'Inspection Attempt Counter',
            'SequenceNumber' => 'Sequence Number',
            'SectionNumber' => 'Section Number',
            'Shape' => 'Shape',
        ];
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