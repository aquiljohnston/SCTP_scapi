<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "ClientTb".
 *
 * @property integer $ClientID
 * @property integer $ClientAccountID
 * @property string $ClientName
 * @property string $ClientContactTitle
 * @property string $ClientContactFName
 * @property string $ClientContactMI
 * @property string $ClientContactLName
 * @property string $ClientPhone
 * @property string $ClientEmail
 * @property string $ClientAddr1
 * @property string $ClientAddr2
 * @property string $ClientCity
 * @property string $ClientState
 * @property string $ClientZip4
 * @property string $ClientTerritory
 * @property integer $ClientActiveFlag
 * @property integer $ClientDivisionsFlag
 * @property string $ClientComment
 * @property string $ClientFilesPath
 * @property string $ClientArchiveFlag
 * @property string $ClientCreateDate
 * @property integer $ClientCreatorUserID
 * @property string $ClientModifiedDate
 * @property integer $ClientModifiedBy
 * @property integer $QBCustomerID
 * @property integer $ReferenceID
 *
 * @property ProjectTb $projectTb
 */
class Client extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ClientTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ClientName'], 'required'],
            [['ClientName', 'ClientContactTitle', 'ClientContactFName', 'ClientContactMI', 'ClientContactLName', 'ClientPhone', 'ClientEmail', 'ClientAddr1', 'ClientAddr2', 'ClientCity', 'ClientState', 'ClientZip4', 'ClientTerritory', 'ClientComment', 'ClientComment', 'ClientArchiveFlag'], 'string'],
            [['ClientAccountID', 'ClientActiveFlag', 'ClientDivisionsFlag',  'ClientCreatorUserID', 'ClientModifiedBy', 'QBCustomerID', 'ReferenceID'], 'integer'],
            [['ClientCreateDate', 'ClientModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ClientID' => 'Client ID',
			'ClientAccountID' => 'Client Account ID',
            'ClientName' => 'Client Name',
            'ClientContactTitle' => 'Client Contact Title',
            'ClientContactFName' => 'Client Contact Fname',
            'ClientContactMI' => 'Client Contact Mi',
            'ClientContactLName' => 'Client Contact Lname',
            'ClientPhone' => 'Client Phone',
            'ClientEmail' => 'Client Email',
            'ClientAddr1' => 'Client Addr1',
            'ClientAddr2' => 'Client Addr2',
            'ClientCity' => 'Client City',
            'ClientState' => 'Client State',
            'ClientZip4' => 'Client Zip4',
            'ClientTerritory' => 'Client Territory',
            'ClientActiveFlag' => 'Client Active Flag',
            'ClientDivisionsFlag' => 'Client Divisions Flag',
            'ClientComment' => 'Client Comment',
            'ClientCreateDate' => 'Client Create Date',
            'ClientCreatorUserID' => 'Client Creator User ID',
            'ClientModifiedDate' => 'Client Modified Date',
            'ClientModifiedBy' => 'Client Modified By',
			'ClientComment' => 'Client Comment',
			'ClientFilesPath' => 'Client Files Path',
			'QBCustomerID' => 'QB Customer ID',
			'ReferenceID' => 'Reference ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectTb()
    {
        return $this->hasOne(ProjectTb::className(), ['ProjectID' => 'ClientID']);
    }
}
