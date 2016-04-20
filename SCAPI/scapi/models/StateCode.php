<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "StateCodeTb".
 *
 * @property string $StateID
 * @property string $StateNames
 * @property string $StateNumber
 */
class StateCode extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'StateCodeTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['StateID', 'StateNames', 'StateNumber'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'StateID' => 'State ID',
            'StateNames' => 'State Names',
            'StateNumber' => 'State Number',
        ];
    }
}
