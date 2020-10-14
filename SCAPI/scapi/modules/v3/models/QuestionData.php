<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "rQuestionData".
 *
 * @property int $ID
 * @property string|null $Question
 * @property string|null $QuestionDescription
 * @property int|null $ResultType
 * @property string|null $RefQuestionID
 * @property string|null $SrvDTLT
 *
 * @property RQuestionDataProject[] $rQuestionDataProjects
 */
class QuestionData extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rQuestionData';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Question', 'QuestionDescription', 'RefQuestionID'], 'string'],
            [['ResultType'], 'integer'],
            [['SrvDTLT'], 'safe'],
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
            'QuestionDescription' => 'Question Description',
            'ResultType' => 'Result Type',
            'RefQuestionID' => 'Ref Question ID',
            'SrvDTLT' => 'Srv Dtlt',
        ];
    }

    /**
     * Gets query for [[RQuestionDataProjects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestionDataProjects()
    {
        return $this->hasMany(QuestionDataProject::className(), ['QuestionID' => 'ID']);
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::className(), ['ProjectID' => 'ProjectID'])
			->via('questionDataProjects');
    }
	
	
}
