<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAccountantSubmit".
 *
 * @property string $ProjectName
 * @property string $ProjectManager
 * @property string $StartDate
 * @property string $EndDate
 * @property string $ApprovedBy
 * @property integer $Total Time Cards
 * @property integer $Approved Time Cards
 * @property string $QBSubmitted
 * @property string $OasisSubmitted
 * @property string $ADPSubmitted
 * @property integer $ProjectID
 */
class AccountantSubmit extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAccountantSubmit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ProjectName', 'ProjectManager', 'ApprovedBy', 'QBSubmitted', 'OasisSubmitted', 'ADPSubmitted'], 'required'],
            [['ProjectName', 'ProjectManager', 'ApprovedBy', 'QBSubmitted', 'OasisSubmitted', 'ADPSubmitted'], 'string'],
            [['StartDate', 'EndDate'], 'safe'],
            [['Total Time Cards', 'Approved Time Cards', 'ProjectID'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ProjectName' => 'Project Name',
            'ProjectManager' => 'Project Manager',
            'StartDate' => 'Start Date',
            'EndDate' => 'End Date',
            'ApprovedBy' => 'Approved By',
            'Total Time Cards' => 'Total  Time  Cards',
            'Approved Time Cards' => 'Approved  Time  Cards',
            'QBSubmitted' => 'Qbsubmitted',
            'OasisSubmitted' => 'Oasis Submitted',
            'ADPSubmitted' => 'Adpsubmitted',
            'ProjectID' => 'Project ID',
        ];
    }
}
