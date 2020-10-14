<?php

namespace app\modules\v2;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
		$this->registerComponents();
		$this->modules = [
			'pge' => [
				'class' => 'app\modules\v2\modules\pge\Module',
			],
			'scana' => [
				'class' => 'app\modules\v2\modules\scana\Module',
			],
			'york' => [
				'class' => 'app\modules\v2\modules\york\Module',
			],
		];
    }
	
	public function registerComponents(){
        \Yii::$app->setComponents([
		//these settings take precedent over the base config
			'user' => [
				'class' => 'app\modules\v2\authentication\CTUser',
				'identityClass' => 'app\modules\v2\models\SCUser',
				'enableAutoLogin' => false,
				'authTimeout' => 43200,
				//'authTimeout' => 30,
				'loginUrl' => null
			]
        ]);
	}
}