<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "rStatusLookup".
 *
 * @property integer $rStatusLookupID
 * @property string $CreatedBy
 * @property string $ModifiedBy
 * @property string $CreatedDateTime
 * @property string $ModifiedDateTime
 * @property string $StatusType
 * @property integer $StatusCode
 * @property string $StatusDescription
 */
class StatusLookup extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rStatusLookup';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CreatedBy', 'ModifiedBy', 'StatusType', 'StatusDescription'], 'string'],
            [['CreatedDateTime', 'ModifiedDateTime'], 'safe'],
            [['StatusCode'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rStatusLookupID' => 'R Status Lookup ID',
            'CreatedBy' => 'Created By',
            'ModifiedBy' => 'Modified By',
            'CreatedDateTime' => 'Created Date Time',
            'ModifiedDateTime' => 'Modified Date Time',
            'StatusType' => 'Status Type',
            'StatusCode' => 'Status Code',
            'StatusDescription' => 'Status Description',
        ];
    }
}
