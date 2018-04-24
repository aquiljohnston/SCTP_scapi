<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "StateCodeTb".
 *
 * @property string $StateID
 * @property string $StateNames
 * @property string $StateNumber
 * @property string $StateArchiveFlag
 * @property string $StateCreateDate
 * @property string $StateCreatedBy
 * @property string $StateModifiedDate
 * @property string $StateModifiedBy
 */
class StateCode extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'StateCodeTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['StateID', 'StateNames', 'StateNumber', 'StateArchiveFlag', 'StateCreateDate', 'StateCreatedBy', 'StateModifiedDate', 'StateModifiedBy'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'StateID' => 'State ID',
            'StateNames' => 'State Names',
            'StateNumber' => 'State Number',
			'StateArchiveFlag' => 'State Archive Flag',
			'StateCreateDate' => 'State Create Date',
			'StateCreatedBy' => 'State Created By',
			'StateModifiedDate' => 'State Modified Date',
			'StateModifiedBy' => 'State Modified By',
			
			
			
        ];
    }
}
