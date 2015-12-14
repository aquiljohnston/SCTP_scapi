<?php

namespace app\models;

use Yii;
use yii\base\Security;

/**
 * This is the model class for table "AuthTb".
 *
 * @property integer $UserID
 * @property string $AuthToken
 */
class Auth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AuthTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID'], 'integer'],
            [['AuthToken'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserId' => 'User ID',
            'AuthToken' => 'Auth Token',
        ];
    }
	
	public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
				//review the algorithm for generateRandomString
                $this->AuthToken = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}
