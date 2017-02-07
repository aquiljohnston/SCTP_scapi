<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tTabletDataInsertArchive".
 *
 * @property integer $TabletDataInsertArchiveID
 * @property string $CreatedUserUID
 * @property string $SvrDTLT
 * @property string $SvrDTLTOffset
 * @property string $TransactionType
 * @property string $InsertedData
 */
class TabletDataInsertArchive extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tTabletDataInsertArchive';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CreatedUserUID', 'TransactionType', 'InsertedData'], 'string'],
            [['SvrDTLT', 'SvrDTLTOffset'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'TabletDataInsertArchiveID' => 'Tablet Data Insert Archive ID',
            'CreatedUserUID' => 'Created User Uid',
            'SvrDTLT' => 'Svr Dtlt',
            'SvrDTLTOffset' => 'Svr Dtltoffset',
            'TransactionType' => 'Transaction Type',
            'InsertedData' => 'Inserted Data',
        ];
    }
}
