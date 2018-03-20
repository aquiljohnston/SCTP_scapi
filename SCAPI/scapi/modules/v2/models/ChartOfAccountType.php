<?php
/**
 * Created by PhpStorm.
 * User: tzhang
 * Date: 2018/1/26
 * Time: 11:12
 */

namespace app\modules\v2\models;

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