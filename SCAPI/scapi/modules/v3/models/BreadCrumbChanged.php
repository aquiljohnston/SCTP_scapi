<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "BreadCrumbChanged".
 *
 * @property int $RowID
 * @property int $OriginalRowID
 * @property string|null $Contract
 * @property int|null $ProjectID
 * @property int $BreadCrumbID
 * @property string $BreadcrumbActivityType
 * @property string $BreadcrumbCreatedUserUID
 * @property string $BreadcrumbSrcDTLT
 * @property string|null $EndDate
 * @property int|null $ttlSecs
 * @property string|null $Duration
 * @property int|null $DataComplete
 * @property int|null $TaskID
 * @property string|null $ChangedOn
 * @property string|null $ChangedBy
 */
class BreadCrumbChanged extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BreadCrumbChanged';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['OriginalRowID', 'BreadCrumbID', 'BreadcrumbActivityType', 'BreadcrumbCreatedUserUID', 'BreadcrumbSrcDTLT'], 'required'],
            [['OriginalRowID', 'ProjectID', 'BreadCrumbID', 'ttlSecs', 'DataComplete', 'TaskID'], 'integer'],
            [['Contract', 'BreadcrumbActivityType', 'BreadcrumbCreatedUserUID', 'ChangedBy'], 'string'],
            [['BreadcrumbSrcDTLT', 'EndDate', 'Duration', 'ChangedOn'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'RowID' => 'Row ID',
            'OriginalRowID' => 'Original Row ID',
            'Contract' => 'Contract',
            'ProjectID' => 'Project ID',
            'BreadCrumbID' => 'Bread Crumb ID',
            'BreadcrumbActivityType' => 'Breadcrumb Activity Type',
            'BreadcrumbCreatedUserUID' => 'Breadcrumb Created User Uid',
            'BreadcrumbSrcDTLT' => 'Breadcrumb Src Dtlt',
            'EndDate' => 'End Date',
            'ttlSecs' => 'Ttl Secs',
            'Duration' => 'Duration',
            'DataComplete' => 'Data Complete',
            'TaskID' => 'Task ID',
            'ChangedOn' => 'Changed On',
            'ChangedBy' => 'Changed By',
        ];
    }
}
