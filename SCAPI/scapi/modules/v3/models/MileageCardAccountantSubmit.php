<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "vMileageCardAccountantSubmit".
 *
 * @property string $ProjectName
 * @property string $ProjectManager
 * @property string $StartDate
 * @property string $EndDate
 * @property string $ApprovedBy
 * @property int $Total Mileage Cards
 * @property int $Approved Mileage Cards
 * @property string $MSDynamicsSubmitted
 * @property string $OasisSubmitted
 * @property string $ADPSubmitted
 * @property int $ProjectID
 */
class MileageCardAccountantSubmit extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vMileageCardAccountantSubmit';
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
            [['Total Mileage Cards', 'Approved Mileage Cards', 'ProjectID'], 'integer'],
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
            'Total Mileage Cards' => 'Total  Mileage  Cards',
            'Approved Mileage Cards' => 'Approved  Mileage  Cards',
            'MSDynamicsSubmitted' => 'Msdynamics Submitted',
            'OasisSubmitted' => 'Oasis Submitted',
            'ADPSubmitted' => 'Adpsubmitted',
            'ProjectID' => 'Project ID',
        ];
    }
}
