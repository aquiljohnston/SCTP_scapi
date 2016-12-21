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
        'ctDevDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=10.0.0.50;Database=CometTracker',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'pgeDevDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_PGE_GIS_DEV',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'ctStageDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=10.0.0.50;Database=CometTracker_Stage',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'pgeStageDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_PGE_GIS_STAGE',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'ctProdDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=10.0.0.51;Database=CometTracker_PROD',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'pgeProdDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=10.0.0.51;Database=vCAT_PGE_GIS_PROD',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
    ],
    'params' => $params,
];
