<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "vAccountantSubmit".
 *
 * @property string $ProjectName
 * @property string $ProjectManager
 * @property string $StartDate
 * @property string $EndDate
 * @property string $ApprovedBy
 * @property int $Total Time Cards
 * @property int $Approved Time Cards
 * @property string $MSDynamicsSubmitted
 * @property string $OasisSubmitted
 * @property string $ADPSubmitted
 * @property int $ProjectID
 */
class AccountantSubmit extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vAccountantSubmit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProjectName', 'ProjectManager', 'ApprovedBy', 'MSDynamicsSubmitted', 'OasisSubmitted', 'ADPSubmitted'], 'required'],
            [['ProjectName', 'ProjectManager', 'ApprovedBy', 'MSDynamicsSubmitted', 'OasisSubmitted', 'ADPSubmitted'], 'string'],
            [['StartDate', 'EndDate'], 'safe'],
            [['Total Time Cards', 'Approved Time Cards', 'ProjectID'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
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
            'MSDynamicsSubmitted' => 'Msdynamics Submitted',
            'OasisSubmitted' => 'Oasis Submitted',
            'ADPSubmitted' => 'Adpsubmitted',
            'ProjectID' => 'Project ID',
        ];
    }
}
