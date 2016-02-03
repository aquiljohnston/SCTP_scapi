<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ClientAccounts".
 *
 * @property double $ClientAccountID
 * @property string $ClientAccountName
 * @property double $ClientAccountNumber
 * @property string $ClientAccountDescriptions
 * @property string $ClientAccountComment
 * @property double $ClientAccountStatus
 */
class ClientAccounts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ClientAccounts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ClientAccountID', 'ClientAccountNumber', 'ClientAccountStatus'], 'number'],
            [['ClientAccountName', 'ClientAccountDescriptions', 'ClientAccountComment'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ClientAccountID' => 'Client Account ID',
            'ClientAccountName' => 'Client Account Name',
            'ClientAccountNumber' => 'Client Account Number',
            'ClientAccountDescriptions' => 'Client Account Descriptions',
            'ClientAccountComment' => 'Client Account Comment',
            'ClientAccountStatus' => 'Client Account Status',
        ];
    }
}
