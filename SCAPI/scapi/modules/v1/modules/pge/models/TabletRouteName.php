<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vTabletRouteName".
 *
 * @property string $MapGridUID
 * @property string $RouteName
 */
class TabletRouteName extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTabletRouteName';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGridUID', 'RouteName'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGridUID' => 'Map Grid Uid',
            'RouteName' => 'Route Name',
        ];
    }
}
