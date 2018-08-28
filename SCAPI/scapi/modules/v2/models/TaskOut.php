<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "tTaskOut2".
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
 * @property int $CGECount
 * @property int $AdHocCount
 * @property int $FieldServiceWorkOrdersSkipped
 * @property int $FieldServiceWorkOrdersCompleted
 * @property int $FieldServiceRemediationsCompleted
 * @property int $ACWorkOrdersSkipped
 * @property int $ACWorkOrdersCompleted
 * @property int $ACRemediationsCompleted
 * @property int $MeterServiceWorkOrdersSkipped
 * @property int $MeterServiceWorkOrdersCompleted
 * @property int $LocatingTicketsCompleted
 * @property int $LocatingServicesCompleted
 * @property int $CGEServicesAttempted
 * @property int $CGEServicesCompleted
 * @property int $CGEBelowGroundGrade1
 * @property int $CGEBelowGroundGrade2
 * @property int $CGEBelowGroundGrade3
 * @property int $CGETotalBelowGround
 * @property int $CGEAboveGroundGrade1
 * @property int $CGEAboveGroundGrade2
 * @property int $CGEAboveGroundGrade3
 * @property int $CGETotalAboveGround
 * @property int $SurveySurveyFootage
 * @property int $SurveyNoOfServices
 * @property int $SurveyCGEs
 * @property int $SurveyBelowGroundGrade1
 * @property int $SurveyBelowGroundGrade2
 * @property int $SurveyBelowGroundGrade3
 * @property int $SurveyTotalBelowGround
 * @property int $SurveyAboveGroundGrade1
 * @property int $SurveyAboveGroundGrade2
 * @property int $SurveyAboveGroundGrade3
 * @property int $SurveyTotalAboveGround
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
        return 'tTaskOut2';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ActivityID', 'CreatedUserID'], 'required'],
            [['ActivityID', 'AboveGroundLeakCount', 'BelowGroundLeakCount', 'ServicesCount', 'FeetOfMain', 'CreatedUserID', 'DeletedFlag', 'FeetOfTransmission', 'FeetOfHighPressure', 'CGECount', 'AdHocCount', 'FieldServiceWorkOrdersSkipped', 'FieldServiceWorkOrdersCompleted', 'FieldServiceRemediationsCompleted', 'ACWorkOrdersSkipped', 'ACWorkOrdersCompleted', 'ACRemediationsCompleted', 'MeterServiceWorkOrdersSkipped', 'MeterServiceWorkOrdersCompleted', 'LocatingTicketsCompleted', 'LocatingServicesCompleted', 'CGEServicesAttempted', 'CGEServicesCompleted', 'CGEBelowGroundGrade1', 'CGEBelowGroundGrade2', 'CGEBelowGroundGrade3', 'CGETotalBelowGround', 'CGEAboveGroundGrade1', 'CGEAboveGroundGrade2', 'CGEAboveGroundGrade3', 'CGETotalAboveGround', 'SurveySurveyFootage', 'SurveyNoOfServices', 'SurveyCGEs', 'SurveyBelowGroundGrade1', 'SurveyBelowGroundGrade2', 'SurveyBelowGroundGrade3', 'SurveyTotalBelowGround', 'SurveyAboveGroundGrade1', 'SurveyAboveGroundGrade2', 'SurveyAboveGroundGrade3', 'SurveyTotalAboveGround'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffSet', 'StartDTLT', 'EndDTLT'], 'safe'],
            [['MapGrid', 'Comments'], 'string'],
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
            'CGECount' => 'Cgecount',
            'AdHocCount' => 'Ad Hoc Count',
            'FieldServiceWorkOrdersSkipped' => 'Field Service Work Orders Skipped',
            'FieldServiceWorkOrdersCompleted' => 'Field Service Work Orders Completed',
            'FieldServiceRemediationsCompleted' => 'Field Service Remediations Completed',
            'ACWorkOrdersSkipped' => 'Acwork Orders Skipped',
            'ACWorkOrdersCompleted' => 'Acwork Orders Completed',
            'ACRemediationsCompleted' => 'Acremediations Completed',
            'MeterServiceWorkOrdersSkipped' => 'Meter Service Work Orders Skipped',
            'MeterServiceWorkOrdersCompleted' => 'Meter Service Work Orders Completed',
            'LocatingTicketsCompleted' => 'Locating Tickets Completed',
            'LocatingServicesCompleted' => 'Locating Services Completed',
            'CGEServicesAttempted' => 'Cgeservices Attempted',
            'CGEServicesCompleted' => 'Cgeservices Completed',
            'CGEBelowGroundGrade1' => 'Cgebelow Ground Grade1',
            'CGEBelowGroundGrade2' => 'Cgebelow Ground Grade2',
            'CGEBelowGroundGrade3' => 'Cgebelow Ground Grade3',
            'CGETotalBelowGround' => 'Cgetotal Below Ground',
            'CGEAboveGroundGrade1' => 'Cgeabove Ground Grade1',
            'CGEAboveGroundGrade2' => 'Cgeabove Ground Grade2',
            'CGEAboveGroundGrade3' => 'Cgeabove Ground Grade3',
            'CGETotalAboveGround' => 'Cgetotal Above Ground',
            'SurveySurveyFootage' => 'Survey Survey Footage',
            'SurveyNoOfServices' => 'Survey No Of Services',
            'SurveyCGEs' => 'Survey Cges',
            'SurveyBelowGroundGrade1' => 'Survey Below Ground Grade1',
            'SurveyBelowGroundGrade2' => 'Survey Below Ground Grade2',
            'SurveyBelowGroundGrade3' => 'Survey Below Ground Grade3',
            'SurveyTotalBelowGround' => 'Survey Total Below Ground',
            'SurveyAboveGroundGrade1' => 'Survey Above Ground Grade1',
            'SurveyAboveGroundGrade2' => 'Survey Above Ground Grade2',
            'SurveyAboveGroundGrade3' => 'Survey Above Ground Grade3',
            'SurveyTotalAboveGround' => 'Survey Total Above Ground',
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
