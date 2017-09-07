<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
		'authManager' => [
            'class' => 'app\rbac\ScDbManager',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
		//database connections
        //Comet Tracker
        'ctDevDb' => $db['ctDevDb'],
		'ctStageDb' => $db['ctStageDb'],
		'ctProdDb' => $db['ctProdDb'],
		//PGE
        'pgeDevDb' => $db['pgeDevDb'],
		'pgeStageDb' => $db['pgeStageDb'],
		'pgeProdDb' => $db['pgeProdDb'],
		//York
        'yorkDevDb' => $db['yorkDevDb'],
		'yorkStageDb' => $db['yorkStageDb'],
		'yorkProdDb' => $db['yorkProdDb'],
		//Dominion
		'dominionStageDb' => $db['dominionStageDb'],
		'dominionProdDb' => $db['dominionProdDb'],
		//Scana
		'scanaDevDb' => $db['scanaDevDb'],
		//'scanaStageDb' => $db['scanaStageDb'],
		//'scanaProdDb' => $db['scanaProdDb'],
		//Demo
        'demoDb' => $db['demoDb'],
		//Azure tests
		'azureDb' => $db['azureDb'],
    ],
    'params' => $params,
];
