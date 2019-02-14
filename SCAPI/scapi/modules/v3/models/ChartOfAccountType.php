<?php

namespace app\modules\v3\models;

/**
 * This is the model class for table "ChartOfAccountTb".
 *
 * @property integer $ChartOfAccountID
 * @property string $ChartOfAccountDescription
 * @property string $ChartOfAccountUnits
 * @property string $ChartOfAccountUsage
 */

class ChartOfAccountType extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'refChartOfAccount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ChartOfAccountDescription', 'ChartOfAccountUnits', 'ChartOfAccountUsage'], 'string'],
            [['ChartOfAccountID'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ChartOfAccountID' => 'Chart Of Account ID',
            'ChartOfAccountDescription' => 'Chart Of Account Description',
            'ChartOfAccountUnits' => 'Chart Of Account Units',
            'ChartOfAccountUsage' => 'Chart Of Account Usage'
        ];
    }
}