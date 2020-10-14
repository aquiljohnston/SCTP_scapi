<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "vGetExpenses".
 *
 * @property int $ID
 * @property int $UserID
 * @property string $UserName
 * @property int $ProjectID
 * @property string $ProjectName
 * @property string $CreatedDate
 * @property int $ChargeAccount
 * @property string $Quantity
 * @property int $IsApproved
 * @property int $IsSubmitted
 */
class GetExpenses extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vGetExpenses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ID', 'ProjectID', 'ChargeAccount', 'Quantity'], 'required'],
            [['ID', 'UserID', 'ProjectID', 'ChargeAccount', 'IsApproved', 'IsSubmitted'], 'integer'],
            [['UserName', 'ProjectName'], 'string'],
            [['CreatedDate'], 'safe'],
            [['Quantity'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'ProjectID' => 'Project ID',
            'ProjectName' => 'Project Name',
            'CreatedDate' => 'Created Date',
            'ChargeAccount' => 'Charge Account',
            'Quantity' => 'Quantity',
            'IsApproved' => 'Is Approved',
            'IsSubmitted' => 'Is Submitted',
        ];
    }
}
