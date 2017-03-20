<?php

namespace app\modules\v1;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
		
		$this->modules = [
			'beta' => [
				'class' => 'app\modules\v1\modules\beta\Module',
			],
			'pge' => [
				'class' => 'app\modules\v1\modules\pge\Module',
			],
			'scana' => [
				'class' => 'app\modules\v1\modules\scana\Module',
			],
		];
    }
}