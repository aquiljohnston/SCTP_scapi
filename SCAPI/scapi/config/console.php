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
        'ctDevDb' => $db['ctDevDb'],
        'pgeDevDb' => $db['pgeDevDb'],
        'yorkDevDb' => $db['yorkDevDb'],
        'ctStageDb' => $db['ctStageDb'],
        'pgeStageDb' => $db['pgeStageDb'],
		'yorkStageDb' => $db['yorkStageDb'],
		'dominionStageDb' => $db['dominionStageDb'],
        'ctProdDb' => $db['ctProdDb'],
        'pgeProdDb' => $db['pgeProdDb'],
		'yorkProdDb' => $db['yorkProdDb'],
        'demoDb' => $db['demoDb'],
    ],
    'params' => $params,
];
