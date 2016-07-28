<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "KeyTb".
 *
 * @property string $KeyID
 * @property string $Key1
 * @property string $Key2
 * @property string $Key3
 * @property string $KeyStatus
 * @property string $KeyComment
 * @property string $KeyArchiveFlag
 * @property string $KeyCreateDate
 * @property integer $KeyCreatedBy
 * @property string $KeyModifiedDate
 * @property integer $KeyModifiedBy
 *
 * @property UserTb[] $userTbs
 */
class Key extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'KeyTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Key1', 'Key2', 'Key3', 'KeyStatus', 'KeyComment', 'KeyArchiveFlag'], 'string'],
			[['KeyCreatedBy', 'KeyModifiedBy'], 'integer'],
            [['KeyCreateDate', 'KeyModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'KeyID' => 'Key ID',
            'Key1' => 'Key1',
            'Key2' => 'Key2',
            'Key3' => 'Key3',
            'KeyStatus' => 'Key Status',
            'KeyComment' => 'Key Comment',
			'KeyArchiveFlag' => 'Key Archive Flag',
            'KeyCreateDate' => 'Key Create Date',
            'KeyCreatedBy' => 'Key Created By',
            'KeyModifiedDate' => 'Key Modified Date',
            'KeyModifiedBy' => 'Key Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTbs()
    {
        return $this->hasMany(UserTb::className(), ['UserKey' => 'KeyID']);
    }
}
