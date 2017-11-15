<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "tWebDataInsertArchive".
 *
 * @property integer $WebDataInsertArchiveID
 * @property string $CreatedUserUID
 * @property string $SvrDTLT
 * @property string $SvrDTLTOffset
 * @property string $TransactionType
 * @property string $InsertedData
 */
class WebDataInsertArchive extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tWebDataInsertArchive';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CreatedUserUID', 'TransactionType', 'InsertedData'], 'string'],
            [['SvrDTLT', 'SvrDTLTOffset'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'WebDataInsertArchiveID' => 'Web Data Insert Archive ID',
            'CreatedUserUID' => 'Created User Uid',
            'SvrDTLT' => 'Svr Dtlt',
            'SvrDTLTOffset' => 'Svr Dtltoffset',
            'TransactionType' => 'Transaction Type',
            'InsertedData' => 'Inserted Data',
        ];
    }
}
