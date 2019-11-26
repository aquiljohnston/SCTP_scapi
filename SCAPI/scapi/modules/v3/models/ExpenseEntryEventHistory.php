<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "ExpenseEntryEventHistory".
 *
 * @property int $Id
 * @property int $ExpenseID
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
 * @property string $CreatedDate
 * @property string $ChangeMadeBy
 * @property string $ChangeDateTime
 * @property string $Change
 * @property string $Comments
 * @property int $UserID
 *
 * @property UserTb $user
 */
class ExpenseEntryEventHistory extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ExpenseEntryEventHistory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ExpenseID', 'ChargeAccount', 'ProjectID', 'IsApproved', 'IsSubmitted', 'UserID'], 'integer'],
            [['Quantity'], 'number'],
            [['Username', 'ApprovedBy', 'SubmittedBy', 'ChangeMadeBy', 'Change', 'Comments'], 'string'],
            [['CreatedDateTime', 'ApprovedDate', 'SubmittedDate', 'CreatedDate', 'ChangeDateTime'], 'safe'],
            [['UserID'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['UserID' => 'UserID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'ExpenseID' => 'Expense ID',
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
            'CreatedDate' => 'Created Date',
            'ChangeMadeBy' => 'Change Made By',
            'ChangeDateTime' => 'Change Date Time',
            'Change' => 'Change',
            'Comments' => 'Comments',
            'UserID' => 'User ID',
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
