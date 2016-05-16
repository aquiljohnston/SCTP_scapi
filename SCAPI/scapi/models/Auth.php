<?php

namespace app\models;

use Yii;
use yii\base\Security;

/**
 * This is the model class for table "AuthTb".
 *
 * @property integer $AuthUserID
 * @property string $AuthToken
 * @property string $AuthCreatedDate
 * @property string $AuthCreatedBy
 * @property string $AuthModifiedDate
 * @property string $AuthModifiedBy
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
            [['AuthToken', 'AuthCreatedBy', 'AuthModifiedBy'], 'string'],
			[['AuthCreatedDate', 'AuthModifiedDate'], 'safe']
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
			'AuthCreatedDate' => 'Auth Created Date',
			'AuthCreatedBy' => 'Auth Created By',
			'AuthModifiedDate' => 'Auth Modified Date',
			'AuthModifiedBy' => 'Auth Modified By',
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
