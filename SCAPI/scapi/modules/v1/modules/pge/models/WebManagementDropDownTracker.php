<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownTracker".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyorInspector
 */
class WebManagementDropDownTracker extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownTracker';
    }
}
