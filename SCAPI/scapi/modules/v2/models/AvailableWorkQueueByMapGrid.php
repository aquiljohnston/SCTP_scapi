<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkQueueByMapGrid".
 *
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $InspectionAttemptCounter
 * @property integer $AvailableWorkOrderCount
 */
class AvailableWorkQueueByMapGrid extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAvailableWorkQueueByMapGrid';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('yorkDevDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid'], 'string'],
            [['ComplianceStart', 'ComplianceEnd'], 'safe'],
            [['InspectionAttemptCounter', 'AvailableWorkOrderCount'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGrid' => 'Map Grid',
            'ComplianceStart' => 'Compliance Start',
            'ComplianceEnd' => 'Compliance End',
            'InspectionAttemptCounter' => 'Inspection Attempt Counter',
            'AvailableWorkOrderCount' => 'Available Work Order Count',
        ];
    }
}
