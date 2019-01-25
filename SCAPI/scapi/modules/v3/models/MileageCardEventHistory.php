<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tMileageCardEventHistory".
 *
 * @property int $ID
 * @property int $MileageCardID
 * @property string $Type
 * @property string $Date
 * @property string $Name
 * @property string $StartDate
 * @property string $EndDate
 * @property string $Comments
 */
class MileageCardEventHistory extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tMileageCardEventHistory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['MileageCardID'], 'integer'],
            [['Type', 'Name', 'Comments'], 'string'],
            [['Date', 'StartDate', 'EndDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'MileageCardID' => 'Mileage Card ID',
            'Type' => 'Type',
            'Date' => 'Date',
            'Name' => 'Name',
            'StartDate' => 'Start Date',
            'EndDate' => 'End Date',
            'Comments' => 'Comments',
        ];
    }
}
