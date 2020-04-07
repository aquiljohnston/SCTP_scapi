<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "Question".
 *
 * @property int $ID
 * @property string|null $Question
 * @property string|null $Value
 * @property string|null $RefProjectID
 * @property string|null $RefQuestionID
 * @property int|null $SCCEmployeeID
 * @property string|null $QuestionUID
 * @property string|null $SrvDTLT
 * @property string|null $Date
 */
class Question extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Question', 'Value', 'RefProjectID', 'RefQuestionID', 'QuestionUID'], 'string'],
            [['SCCEmployeeID'], 'integer'],
            [['SrvDTLT', 'Date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Question' => 'Question',
            'Value' => 'Value',
            'RefProjectID' => 'Ref Project ID',
            'RefQuestionID' => 'Ref Question ID',
            'SCCEmployeeID' => 'Sccemployee ID',
            'QuestionUID' => 'Question Uid',
            'SrvDTLT' => 'Srv Dtlt',
            'Date' => 'Date',
        ];
    }
}
