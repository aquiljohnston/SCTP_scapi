<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "PTOHistory".
 *
 * @property int $ID
 * @property float|null $Quantity
 * @property string|null $Memo
 * @property string|null $StartDate
 * @property string|null $EndDate
 * @property int|null $SCCEmployeeID
 * @property int|null $IsApproved
 * @property int|null $ApprovedByID
 * @property string|null $ApprovedDateTime
 * @property int|null $IsSubmitted
 * @property int|null $SubmittedByID
 * @property string|null $SubmittedDateTime
 * @property string|null $SrcCreatedDateTime
 * @property string $SrvCreatedDateTime
 * @property string|null $RefProjectID
 * @property string|null $PTOUID
 * @property int|null $TimeCardID
 * @property float|null $PreviousBalance
 * @property float|null $NewBalance
 */
class PTOHistory extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'PTOHistory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Quantity', 'PreviousBalance', 'NewBalance'], 'number'],
            [['Memo', 'RefProjectID', 'PTOUID'], 'string'],
            [['StartDate', 'EndDate', 'ApprovedDateTime', 'SubmittedDateTime', 'SrcCreatedDateTime', 'SrvCreatedDateTime'], 'safe'],
            [['SCCEmployeeID', 'IsApproved', 'ApprovedByID', 'IsSubmitted', 'SubmittedByID', 'TimeCardID'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Quantity' => 'Quantity',
            'Memo' => 'Memo',
            'StartDate' => 'Start Date',
            'EndDate' => 'End Date',
            'SCCEmployeeID' => 'Sccemployee ID',
            'IsApproved' => 'Is Approved',
            'ApprovedByID' => 'Approved By ID',
            'ApprovedDateTime' => 'Approved Date Time',
            'IsSubmitted' => 'Is Submitted',
            'SubmittedByID' => 'Submitted By ID',
            'SubmittedDateTime' => 'Submitted Date Time',
            'SrcCreatedDateTime' => 'Src Created Date Time',
            'SrvCreatedDateTime' => 'Srv Created Date Time',
            'RefProjectID' => 'Ref Project ID',
            'PTOUID' => 'Ptouid',
            'TimeCardID' => 'Time Card ID',
            'PreviousBalance' => 'Previous Balance',
            'NewBalance' => 'New Balance',
        ];
    }
}
