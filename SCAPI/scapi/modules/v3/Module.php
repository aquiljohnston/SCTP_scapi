<?php

namespace app\modules\v3;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
		$this->registerComponents();
		$this->modules = [
			'scana' => [
				'class' => 'app\modules\v3\modules\scana\Module',
			],
			'york' => [
				'class' => 'app\modules\v3\modules\york\Module',
			],
		];
    }
	
	public function registerComponents(){
        \Yii::$app->setComponents([
		//these settings take precedent over the base config
               'user' => [
				'class' => 'app\modules\v3\authentication\CTUser',
				'identityClass' => 'app\modules\v3\models\SCUser',
				'enableAutoLogin' => false,
				'authTimeout' => 43200,
				'loginUrl' => null
			]
        ]);
	}
}