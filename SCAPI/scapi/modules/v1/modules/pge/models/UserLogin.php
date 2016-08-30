<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vUserLogin".
 *
 * @property string $UserUID
 * @property string $UserLoginID
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $UserLANID
 * @property integer $TabletLogin
 */
class UserLogin extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vUserLogin';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('pgeDevDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserUID', 'UserLoginID', 'UserFirstName', 'UserLastName', 'UserLANID'], 'string'],
            [['TabletLogin'], 'required'],
            [['TabletLogin'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserUID' => 'User Uid',
            'UserLoginID' => 'User Login ID',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'UserLANID' => 'User Lanid',
            'TabletLogin' => 'Tablet Login',
        ];
    }
}
