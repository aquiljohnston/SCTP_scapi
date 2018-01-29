<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tTabletJSONDataInsertError".
 *
 * @property integer $tJSONDataInsertErrorID
 * @property string $SvrDTLT
 * @property string $SvrDTLT_Offset
 * @property string $InsertedData
 * @property string $InsertedData2
 * @property string $InsertedData3
 * @property string $InsertedData4
 * @property string $InsertedData5
 * @property integer $ErrorNumber
 * @property string $ErrorMessage
 */
class TabletJSONDataInsertError extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tTabletJSONDataInsertError';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['SvrDTLT', 'SvrDTLT_Offset'], 'safe'],
            [['InsertedData', 'InsertedData2', 'InsertedData3', 'InsertedData4', 'InsertedData5', 'ErrorMessage'], 'string'],
            [['ErrorNumber'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tJSONDataInsertErrorID' => 'T Jsondata Insert Error ID',
            'SvrDTLT' => 'Svr Dtlt',
            'SvrDTLT_Offset' => 'Svr Dtlt  Offset',
            'InsertedData' => 'Inserted Data',
            'InsertedData2' => 'Inserted Data2',
            'InsertedData3' => 'Inserted Data3',
            'InsertedData4' => 'Inserted Data4',
            'InsertedData5' => 'Inserted Data5',
            'ErrorNumber' => 'Error Number',
            'ErrorMessage' => 'Error Message',
        ];
    }
}
