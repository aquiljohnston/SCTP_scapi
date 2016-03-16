<?php

namespace app\models;

use Yii;
use yii\base\Security;

/**
 * This is the model class for table "AuthTb".
 *
 * @property integer $AuthUserID
 * @property string $AuthToken
 */
class Auth extends BaseActiveRecord
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
            [['AuthUserID'], 'integer'],
            [['AuthToken'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AuthUserId' => 'Auth User ID',
            'AuthToken' => 'Auth Token',
        ];
    }
	
	public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
				//review the algorithm for generateRandomString
				$authArray = Auth::find()
					->where(['AuthUserID' => $this->AuthUserID])
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
