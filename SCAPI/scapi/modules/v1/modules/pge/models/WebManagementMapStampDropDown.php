<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementMapStampDropDownv".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string ${'Map/Plat'}
 * @property string $Date
 */
class WebManagementMapStampDropDown extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementMapStampDropDown';
    }

}
