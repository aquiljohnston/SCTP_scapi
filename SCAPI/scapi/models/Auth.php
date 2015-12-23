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
				$authArray = Auth::find()
					->where(['UserID' => $this->UserID])
					->all();
				foreach($authArray as $a)
				{
					$a -> delete();
				}
                $this->AuthToken = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}
