<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAssignedWorkQueue".
 *
 * @property int $WorkOrderID
 * @property string $ClientWorkOrderID
 * @property int $WorkQueueID
 * @property int $CreatedBy
 * @property int $ModifiedBy
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
 * @property int $CompletedFlag
 * @property string $CompletedDate
 * @property int $InspectionAttemptCounter
 * @property string $SequenceNumber
 * @property string $SectionNumber
 * @property string $Shape
 * @property string $BillingCode
 * @property int $WorkQueueStatus
 * @property string $AssignedTo
 * @property string $AssignedBy
 * @property int $AssignedByID
 * @property int $AssignedToID
 * @property string $Address
 * @property string $MeterLocation
 * @property double $PipelineFootage
 * @property string $SpecialInstructions
 * @property string $OfficeName
 * @property int $AttemptCounter
 * @property string $ScheduledDispatchDate
 * @property string $CGEReason
 */
class AssignedWorkQueue extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vAssignedWorkQueue';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['WorkOrderID', 'WorkQueueID'], 'required'],
            [['WorkOrderID', 'WorkQueueID', 'CreatedBy', 'ModifiedBy', 'CompletedFlag', 'InspectionAttemptCounter', 'WorkQueueStatus', 'AssignedByID', 'AssignedToID', 'AttemptCounter'], 'integer'],
            [['ClientWorkOrderID', 'InspectionType', 'HouseNumber', 'Street', 'AptSuite', 'City', 'State', 'Zip', 'MeterNumber', 'MeterLocationDesc', 'LocationType', 'MapGrid', 'AccountNumber', 'AccountName', 'AccountTelephoneNumber', 'Comments', 'SequenceNumber', 'SectionNumber', 'Shape', 'BillingCode', 'AssignedTo', 'AssignedBy', 'Address', 'MeterLocation', 'SpecialInstructions', 'OfficeName', 'CGEReason'], 'string'],
            [['CreatedDateTime', 'ModifiedDateTime', 'ComplianceStart', 'ComplianceEnd', 'CompletedDate', 'ScheduledDispatchDate'], 'safe'],
            [['LocationLatitude', 'LocationLongitude', 'MapLatitudeBegin', 'MapLongitudeBegin', 'MapLatitudeEnd', 'MapLongitudeEnd', 'PipelineFootage'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'WorkOrderID' => 'Work Order ID',
            'ClientWorkOrderID' => 'Client Work Order ID',
            'WorkQueueID' => 'Work Queue ID',
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
            'BillingCode' => 'Billing Code',
            'WorkQueueStatus' => 'Work Queue Status',
            'AssignedTo' => 'Assigned To',
            'AssignedBy' => 'Assigned By',
            'AssignedByID' => 'Assigned By ID',
            'AssignedToID' => 'Assigned To ID',
            'Address' => 'Address',
            'MeterLocation' => 'Meter Location',
            'PipelineFootage' => 'Pipeline Footage',
            'SpecialInstructions' => 'Special Instructions',
            'OfficeName' => 'Office Name',
            'AttemptCounter' => 'Attempt Counter',
            'ScheduledDispatchDate' => 'Scheduled Dispatch Date',
            'CGEReason' => 'Cgereason',
        ];
    }
}
