<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vInActiveUsers".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $Name
 * @property string $UserAppRoleType
 */
class InActiveUsers extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vInActiveUsers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserName', 'Name', 'UserAppRoleType'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'Name' => 'Name',
            'UserAppRoleType' => 'User App Role Type',
        ];
    }
}
