<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tTabletDataInsertBreadcrumbArchive".
 *
 * @property integer $tTabletDataInsertArchiveBreadcrumbID
 * @property integer $ClientID
 * @property integer $UserID
 * @property string $SvrDTLT
 * @property string $SvrDTLT_Offset
 * @property string $TransactionType
 * @property string $InsertedData
 */
class TabletDataInsertBreadcrumbArchive extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tTabletDataInsertBreadcrumbArchive';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ClientID'], 'integer'],
            [['SvrDTLT', 'SvrDTLT_Offset'], 'safe'],
            [['TransactionType', 'InsertedData', 'UserUID'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tTabletDataInsertArchiveBreadcrumbID' => 'T Tablet Data Insert Archive Breadcrumb ID',
            'ClientID' => 'Client ID',
            'UserUID' => 'User UID',
            'SvrDTLT' => 'Svr Dtlt',
            'SvrDTLT_Offset' => 'Svr Dtlt  Offset',
            'TransactionType' => 'Transaction Type',
            'InsertedData' => 'Inserted Data',
        ];
    }
}
