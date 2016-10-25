<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tTabletJSONDataInsertError".
 *
 * @property integer $tJSONDataInsertErrorID
 * @property string $SvrDTLT
 * @property string $SvrDTLT_Offset
 * @property string $InsertedData
 * @property integer $ErrorNumber
 * @property string $ErrorMessage
 */
class TabletJSONDataInsertError extends \app\modules\v1\models\BaseActiveRecord
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
            [['InsertedData', 'ErrorMessage'], 'string'],
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
            'ErrorNumber' => 'Error Number',
            'ErrorMessage' => 'Error Message',
        ];
    }
}
