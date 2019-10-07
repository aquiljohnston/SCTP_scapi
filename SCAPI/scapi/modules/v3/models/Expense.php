<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "Expense".
 *
 * @property int $ID
 * @property int $ChargeAccount
 * @property string $Quantity
 * @property string $Username
 * @property string $CreatedDate
 * @property int $ProjectID
 * @property int $IsApproved
 * @property string $ApprovedBy
 * @property string $ApprovedDate
 * @property int $IsSubmitted
 * @property string $SubmittedBy
 * @property string $SubmittedDate
 */
class Expense extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Expense';
    }
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ChargeAccount', 'Quantity', 'Username'], 'required'],
            [['ChargeAccount', 'ProjectID', 'IsApproved', 'IsSubmitted'], 'integer'],
            [['Quantity'], 'number'],
            [['Username', 'ApprovedBy', 'SubmittedBy'], 'string'],
            [['CreatedDate', 'ApprovedDate', 'SubmittedDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ChargeAccount' => 'Charge Account',
            'Quantity' => 'Quantity',
            'Username' => 'Username',
            'CreatedDate' => 'Created Date',
            'ProjectID' => 'Project ID',
            'IsApproved' => 'Is Approved',
            'ApprovedBy' => 'Approved By',
            'ApprovedDate' => 'Approved Date',
            'IsSubmitted' => 'Is Submitted',
            'SubmittedBy' => 'Submitted By',
            'SubmittedDate' => 'Submitted Date',
        ];
    }
}
