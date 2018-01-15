<?php

namespace app\modules\v2;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
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
}