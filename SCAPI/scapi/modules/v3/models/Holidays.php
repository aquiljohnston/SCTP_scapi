<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "Holidays".
 *
 * @property int $ID
 * @property string|null $Name
 * @property string|null $HolidayDate
 */
class Holidays extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Holidays';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Name'], 'string'],
            [['HolidayDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Name' => 'Name',
            'HolidayDate' => 'Holiday Date',
        ];
    }
}
