<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "history.HistoryAuth_Assignment".
 *
 * @property int $ID
 * @property string $DateAdded
 * @property int $UserID
 * @property string $Token
 * @property string $ArchiveFlag
 * @property string $CreateDate
 * @property string $CreatedBy
 * @property string $ModifiedDate
 * @property string $ModifiedBy
 * @property int $TimeOut
 * @property string $HistItem_Name
 * @property string $HistUser_Id
 * @property int $HistCreated_At
 */
class HistoryAuth_Assignment extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history.HistoryAuth_Assignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['DateAdded', 'CreateDate', 'ModifiedDate'], 'safe'],
            [['UserID', 'TimeOut', 'HistCreated_At'], 'integer'],
            [['Token', 'ArchiveFlag', 'CreatedBy', 'ModifiedBy', 'HistItem_Name', 'HistUser_Id'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'DateAdded' => 'Date Added',
            'UserID' => 'User ID',
            'Token' => 'Token',
            'ArchiveFlag' => 'Archive Flag',
            'CreateDate' => 'Create Date',
            'CreatedBy' => 'Created By',
            'ModifiedDate' => 'Modified Date',
            'ModifiedBy' => 'Modified By',
            'TimeOut' => 'Time Out',
            'HistItem_Name' => 'Hist Item  Name',
            'HistUser_Id' => 'Hist User  ID',
            'HistCreated_At' => 'Hist Created  At',
        ];
    }
}
