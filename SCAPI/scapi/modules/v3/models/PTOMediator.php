<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "PTOMediator".
 *
 * @property int $ID
 * @property float|null $PendingBalance
 * @property float|null $DeltaChange
 * @property string $DeltaTimeStamp
 * @property string|null $SyncDateTime
 * @property int|null $IsInSync
 * @property int|null $PTOID
 * @property int|null $UserID
 *
 * @property PTO $pTO
 */
class PTOMediator extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'PTOMediator';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PendingBalance', 'DeltaChange'], 'number'], 	
            [['DeltaTimeStamp', 'SyncDateTime'], 'safe'],
            [['IsInSync', 'PTOID', 'UserID'], 'integer'],
            [['PTOID'], 'exist', 'skipOnError' => true, 'targetClass' => PTO::className(), 'targetAttribute' => ['PTOID' => 'ID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'PendingBalance' => 'Pending Balance',
            'DeltaChange' => 'Delta Change',
            'DeltaTimeStamp' => 'Delta Time Stamp',
            'SyncDateTime' => 'Sync Date Time',
            'IsInSync' => 'Is In Sync',
            'PTOID' => 'Ptoid',
            'UserID' => 'User ID',
        ];
    }

    /**
     * Gets query for [[PTO]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPTO()
    {
        return $this->hasOne(PTO::className(), ['ID' => 'PTOID']);
    }
}
