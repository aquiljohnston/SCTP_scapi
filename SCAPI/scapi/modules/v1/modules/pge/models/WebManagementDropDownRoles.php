<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownRoles".
 *
 * @property string $RoleName
 */
class WebManagementDropDownRoles extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownRoles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['RoleName'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'RoleName' => 'Role Name',
        ];
    }
}
