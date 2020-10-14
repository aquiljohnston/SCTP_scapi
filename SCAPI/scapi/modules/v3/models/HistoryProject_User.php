<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "history.HistoryProject_User_Tb".
 *
 * @property int $HistProjUserID
 * @property int $HistProjUserUserID
 * @property int $HistProjUserProjectID
 * @property string $HistProjUserProjectRoles
 * @property string $HistProjUserComment
 * @property string $HistProjUserArchiveFlag
 * @property string $HistProjUserCreateDate
 * @property string $HistProjUserCreatedBy
 * @property string $HistProjUserModifiedDate
 * @property string $HistProjUserModifiedBy
 */
class HistoryProject_User extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history.HistoryProject_User_Tb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['HistProjUserID', 'HistProjUserUserID', 'HistProjUserProjectID'], 'integer'],
            [['HistProjUserProjectRoles', 'HistProjUserComment', 'HistProjUserArchiveFlag', 'HistProjUserCreatedBy', 'HistProjUserModifiedBy'], 'string'],
            [['HistProjUserCreateDate', 'HistProjUserModifiedDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HistProjUserID' => 'Hist Proj User ID',
            'HistProjUserUserID' => 'Hist Proj User User ID',
            'HistProjUserProjectID' => 'Hist Proj User Project ID',
            'HistProjUserProjectRoles' => 'Hist Proj User Project Roles',
            'HistProjUserComment' => 'Hist Proj User Comment',
            'HistProjUserArchiveFlag' => 'Hist Proj User Archive Flag',
            'HistProjUserCreateDate' => 'Hist Proj User Create Date',
            'HistProjUserCreatedBy' => 'Hist Proj User Created By',
            'HistProjUserModifiedDate' => 'Hist Proj User Modified Date',
            'HistProjUserModifiedBy' => 'Hist Proj User Modified By',
        ];
    }
}
