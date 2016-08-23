<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownEmployeeType".
 *
 * @property string $FieldDescription
 * @property integer $SortSeq
 */
class WebManagementDropDownEmployeeType extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownEmployeeType';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('pgeDevDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['FieldDescription'], 'string'],
            [['SortSeq'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'FieldDescription' => 'Field Description',
            'SortSeq' => 'Sort Seq',
        ];
    }
}
