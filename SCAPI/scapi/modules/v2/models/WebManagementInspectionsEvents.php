<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vWebManagementInspectionsEvents".
 *
 * @property string $EventType
 * @property string $Reason
 * @property string $Photo
 * @property string $Comments
 * @property integer $InspectionID
 */
class WebManagementInspectionsEvents extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementInspectionsEvents';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EventType', 'Reason', 'Photo', 'Comments'], 'string'],
            [['InspectionID'], 'required'],
            [['InspectionID'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EventType' => 'Event Type',
            'Reason' => 'Reason',
            'Photo' => 'Photo',
            'Comments' => 'Comments',
            'InspectionID' => 'Inspection ID',
        ];
    }
}
