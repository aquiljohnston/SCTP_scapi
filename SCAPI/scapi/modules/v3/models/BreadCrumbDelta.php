<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "BreadCrumbDelta".
 *
 * @property int $RowID
 * @property int $OriginalRowID
 * @property int|null $ProjectID
 * @property int $BreadCrumbID
 * @property string $Activity
 * @property string $UserName
 * @property string $BreadcrumbSrcDTLT
 * @property string|null $EndDate
 * @property int|null $ttlSecs
 * @property string|null $Duration
 * @property int|null $TaskID
 * @property string|null $ChangedOn
 * @property string|null $ChangedBy
 */
class BreadCrumbDelta extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BreadCrumbDelta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['OriginalRowID', 'BreadCrumbID', 'Activity', 'UserName', 'BreadcrumbSrcDTLT'], 'required'],
            [['OriginalRowID', 'ProjectID', 'BreadCrumbID', 'ttlSecs', 'TaskID'], 'integer'],
            [['Activity', 'UserName', 'ChangedBy'], 'string'],
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
            'ProjectID' => 'Project ID',
            'BreadCrumbID' => 'Bread Crumb ID',
            'Activity' => 'Activity',
            'UserName' => 'User Name',
            'BreadcrumbSrcDTLT' => 'Breadcrumb Src Dtlt',
            'EndDate' => 'End Date',
            'ttlSecs' => 'Ttl Secs',
            'Duration' => 'Duration',
            'TaskID' => 'Task ID',
            'ChangedOn' => 'Changed On',
            'ChangedBy' => 'Changed By',
        ];
    }
}
