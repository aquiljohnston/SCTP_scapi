<?php

namespace app\modules\v3\models;

use Yii;
use yii\base\Security;

/**
 * This is the model class for table "AuthTb".
 *
 * @property integer $AuthUserID
 * @property string $AuthToken
 * @property string $AuthCreateDate
 * @property string $AuthCreatedBy
 * @property string $AuthModifiedDate
 * @property string $AuthModifiedBy
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
            [['AuthUserID', 'AuthTimeout'], 'integer'],
            [['AuthToken', 'AuthCreatedBy', 'AuthModifiedBy'], 'string'],
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
				//DELETE any existing records
				$authArray = Auth::find()
					->where(['AuthUserID' => $this->AuthUserID])
					->all();
				foreach($authArray as $a)
				{
					$a -> delete();
				}
            }
            return true;
        }
        return false;
    }
}
