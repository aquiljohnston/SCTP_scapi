<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAssignedWorkQueueBySection".
 *
 * @property string $MapGrid
 * @property string $LocationType
 * @property string $SectionNumber
 * @property integer $AssignedWorkQueueCount
 * @property string $AssignedCount
 * @property string $UIDList
 * @property string $SearchString
 * @property string $Percent Completed
 * @property integer $Total
 * @property integer $Remaining
 */
class AssignedWorkQueueBySection extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAssignedWorkQueueBySection';
    }
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid', 'LocationType', 'SectionNumber', 'AssignedCount', 'UIDList', 'SearchString'], 'string'],
            [['AssignedWorkQueueCount', 'Total', 'Remaining'], 'integer'],
            [['AssignedCount'], 'required'],
            [['Percent Completed'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGrid' => 'Map Grid',
            'LocationType' => 'Location Type',
            'SectionNumber' => 'Section Number',
            'AssignedWorkQueueCount' => 'Assigned Work Queue Count',
            'AssignedCount' => 'Assigned Count',
            'UIDList' => 'Uidlist',
            'SearchString' => 'Search String',
            'Percent Completed' => 'Percent  Completed',
            'Total' => 'Total',
            'Remaining' => 'Remaining',
        ];
    }
}
