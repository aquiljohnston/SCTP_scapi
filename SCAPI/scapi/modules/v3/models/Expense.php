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
 * @property string $CreatedDateTime
 * @property int $ProjectID
 * @property int $IsApproved
 * @property string $ApprovedBy
 * @property string $ApprovedDate
 * @property int $IsSubmitted
 * @property string $SubmittedBy
 * @property string $SubmittedDate
 * @property int $UserID
 * @property string $CreatedDate
 *
 * @property UserTb $user
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
            [['ChargeAccount', 'ProjectID', 'IsApproved', 'IsSubmitted', 'UserID'], 'integer'],
            [['Quantity'], 'number'],
            [['Username', 'ApprovedBy', 'SubmittedBy'], 'string'],
            [['CreatedDateTime', 'ApprovedDate', 'SubmittedDate', 'CreatedDate'], 'safe'],
            [['CreatedDate', 'ProjectID', 'UserID'], 'unique', 'targetAttribute' => ['CreatedDate', 'ProjectID', 'UserID']],
            [['UserID'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['UserID' => 'UserID']],
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
            'CreatedDateTime' => 'Created Date Time',
            'ProjectID' => 'Project ID',
            'IsApproved' => 'Is Approved',
            'ApprovedBy' => 'Approved By',
            'ApprovedDate' => 'Approved Date',
            'IsSubmitted' => 'Is Submitted',
            'SubmittedBy' => 'Submitted By',
            'SubmittedDate' => 'Submitted Date',
            'UserID' => 'User ID',
            'CreatedDate' => 'Created Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'UserID']);
    }
}
