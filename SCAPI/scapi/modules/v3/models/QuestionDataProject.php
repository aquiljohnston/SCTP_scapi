<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "rQuestionData_Project".
 *
 * @property int $ID
 * @property int $QuestionID
 * @property int|null $ProjectID
 * @property string|null $SrvDTLT
 *
 * @property ProjectTb $project
 * @property RQuestionData $question
 */
class QuestionDataProject extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rQuestionData_Project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['QuestionID'], 'required'],
            [['QuestionID', 'ProjectID'], 'integer'],
            [['SrvDTLT'], 'safe'],
            [['QuestionID'], 'exist', 'skipOnError' => true, 'targetClass' => QuestionData::className(), 'targetAttribute' => ['QuestionID' => 'ID']],
            [['ProjectID'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectTb::className(), 'targetAttribute' => ['ProjectID' => 'ProjectID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'QuestionID' => 'Question ID',
            'ProjectID' => 'Project ID',
            'SrvDTLT' => 'Srv Dtlt',
        ];
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(ProjectTb::className(), ['ProjectID' => 'ProjectID']);
    }

    /**
     * Gets query for [[Question]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(QuestionData::className(), ['ID' => 'QuestionID']);
    }
}
