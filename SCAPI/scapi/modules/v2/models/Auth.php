<?php

namespace app\modules\v2\models;

use Yii;
use yii\base\Security;

/**
 * This is the model class for table "AuthTb".
 *
 * @property integer $AuthUserID
 * @property string $AuthToken
 * @property string $AuthCreateDate
 * @property integer $AuthCreatedBy
 * @property string $AuthModifiedDate
 * @property integer $AuthModifiedBy
 * @property integer $AuthTimeout
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
            [['AuthUserID', 'AuthCreatedBy', 'AuthModifiedBy', 'AuthTimeout'], 'integer'],
            [['AuthToken'], 'string'],
			[['AuthCreateDate', 'AuthModifiedDate'], 'safe']
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
			'AuthCreateDate' => 'Auth Create Date',
			'AuthCreatedBy' => 'Auth Created By',
			'AuthModifiedDate' => 'Auth Modified Date',
			'AuthModifiedBy' => 'Auth Modified By',
			'AuthTimeout' => 'Auth Timeout',
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
				$this->AuthTimeout = time() + Yii::$app->user->authTimeout;
            }
            return true;
        }
        return false;
    }
}
