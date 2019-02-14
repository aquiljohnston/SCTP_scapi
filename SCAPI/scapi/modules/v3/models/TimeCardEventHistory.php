<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tTimeCardEventHistory".
 *
 * @property integer $ID
 * @property integer $TimeCardID
 * @property string $Type
 * @property string $Date
 * @property string $Name
 * @property string $StartDate
 * @property string $EndDate
 * @property string $Comments
 */
class TimeCardEventHistory extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tTimeCardEventHistory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['TimeCardID'], 'integer'],
            [['Type', 'Name', 'Comments'], 'string'],
            [['Date', 'StartDate', 'EndDate'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'TimeCardID' => 'Time Card ID',
            'Type' => 'Type',
            'Date' => 'Date',
            'Name' => 'Name',
            'StartDate' => 'Start Date',
            'EndDate' => 'End Date',
            'Comments' => 'Comments',
        ];
    }
}
