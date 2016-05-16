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
 * @property double $ClientAccountsArchiveFlag
 * @property double $ClientAccountCreatedDate
 * @property double $ClientAccountCreatedBy
 * @property double $ClientAccountModifiedDate
 * @property double $ClientAccountModifiedBy
 */
class ClientAccounts extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ClientAccountsTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ClientAccountID', 'ClientAccountNumber', 'ClientAccountStatus'], 'number'],
            [['ClientAccountName', 'ClientAccountDescriptions', 'ClientAccountComment', 'ClientAccountsArchiveFlag',
				'ClientAccountCreatedDate', 'ClientAccountCreatedBy', 'ClientAccountModifiedDate', 'ClientAccountModifiedBy'], 'string']
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
			'ClientAccountsArchiveFlag' => 'Client Accounts Archive Flag',
			'ClientAccountCreatedDate' => 'Client Account Created Date',
			'ClientAccountCreatedBy' => 'Client Account Created By',
			'ClientAccountModifiedDate' => 'Client Account Modified Date',
			'ClientAccountModifiedBy' => 'Client Account Modified By',
        ];
    }
}
