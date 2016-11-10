<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "rCityCounty".
 *
 * @property integer $CityCountyID
 * @property string $City
 * @property string $County
 * @property string $CountyCode
 */
class CityCounty extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rCityCounty';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CityCountyID',], 'integer'],
            [['City', 'County','CountyCode'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'CityCountyID' => 'City County ID',
            'City' => 'City',
            'County' => 'County',
            'CountyCode' => 'County Code',

        ];
    }
}
