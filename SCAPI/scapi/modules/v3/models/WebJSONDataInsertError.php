<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tWebJSONDataInsertError".
 *
 * @property integer $tJSONDataInsertErrorID
 * @property string $SvrDTLT
 * @property string $SvrDTLT_Offset
 * @property string $InsertedData
 * @property integer $ErrorNumber
 * @property string $ErrorMessage
 * @property string $InsertedData2
 * @property string $InsertedData3
 * @property string $InsertedData4
 * @property string $InsertedData5
 */
class WebJSONDataInsertError extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tWebJSONDataInsertError';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['SvrDTLT', 'SvrDTLT_Offset'], 'safe'],
            [['InsertedData', 'ErrorMessage', 'InsertedData2', 'InsertedData3', 'InsertedData4', 'InsertedData5'], 'string'],
            [['ErrorNumber'], 'integer'],
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
            'ErrorNumber' => 'Error Number',
            'ErrorMessage' => 'Error Message',
            'InsertedData2' => 'Inserted Data2',
            'InsertedData3' => 'Inserted Data3',
            'InsertedData4' => 'Inserted Data4',
            'InsertedData5' => 'Inserted Data5',
        ];
    }
}
