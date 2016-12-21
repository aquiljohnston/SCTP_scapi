<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementUserWorkCenter".
 *
 * @property string $WorkCenter
 */
class WebManagementUserWorkCenter extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementUserWorkCenter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WorkCenter'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'WorkCenter' => 'Work Center',
        ];
    }
}
