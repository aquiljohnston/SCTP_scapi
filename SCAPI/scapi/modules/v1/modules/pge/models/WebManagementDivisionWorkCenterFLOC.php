<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDivisionWorkCenterFLOC".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string $FLOC
 */
class WebManagementDivisionWorkCenterFLOC extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDivisionWorkCenterFLOC';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Division', 'WorkCenter', 'FLOC'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
            'FLOC' => 'Floc',
        ];
    }
}
