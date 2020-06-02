<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "ProjectConfiguration".
 *
 * @property int $ID
 * @property int|null $ProjectID
 * @property string|null $ProjectReferenceID
 * @property string|null $CreatedDate
 * @property string|null $CreatedBy
 * @property string|null $ModifiedBy
 * @property string|null $ModifiedDate
 * @property int|null $IsEndOfDayTaskOut
 *
 * @property ProjectTb $project
 */
class ProjectConfiguration extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ProjectConfiguration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProjectID', 'IsEndOfDayTaskOut'], 'integer'],
            [['ProjectReferenceID', 'CreatedBy', 'ModifiedBy'], 'string'],
            [['CreatedDate', 'ModifiedDate'], 'safe'],
            [['ProjectID'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['ProjectID' => 'ProjectID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ProjectID' => 'Project ID',
            'ProjectReferenceID' => 'Project Reference ID',
            'CreatedDate' => 'Created Date',
            'CreatedBy' => 'Created By',
            'ModifiedBy' => 'Modified By',
            'ModifiedDate' => 'Modified Date',
            'IsEndOfDayTaskOut' => 'Is End Of Day Task Out',
        ];
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['ProjectID' => 'ProjectID']);
    }
}
