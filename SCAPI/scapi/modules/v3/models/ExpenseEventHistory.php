<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "ExpenseEventHistory".
 *
 * @property int $ID
 * @property int $ExpenseID
 * @property string $Type
 * @property string $Date
 * @property string $Name
 * @property string $StartDate
 * @property string $EndDate
 * @property string $Comments
 */
class ExpenseEventHistory extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ExpenseEventHistory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ExpenseID'], 'integer'],
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
            'ExpenseID' => 'Expense ID',
            'Type' => 'Type',
            'Date' => 'Date',
            'Name' => 'Name',
            'StartDate' => 'Start Date',
            'EndDate' => 'End Date',
            'Comments' => 'Comments',
        ];
    }
}
