<?php

use app\authentication\CTUser;

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'scapi',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'HhN8NbISberb6j0ISaQ8A9WjXeoGgXec',
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
			'class' => 'app\authentication\CTUser',
            'identityClass' => 'app\models\SCUser',
            'enableAutoLogin' => false,
			'authTimeout' => 28800,
			//'authTimeout' => 15,
			'loginUrl' => null
        ],
        'errorHandler' => [
            //'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','trace'],
                ],
            ],
        ],
        //'db' => require(__DIR__ . '/db.php'),
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=192.168.20.46;Database=CometTracker',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'dbQA' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=192.168.20.46;Database=CometTrackerQA',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'scanaDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=192.168.20.46;Database=CT_SCANA',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
		'scanaQADb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=192.168.20.46;Database=CT_SCANAQA',
			'username' => 'ApplicationCometTracker',
			'password' => '321cba',
			'charset' => 'utf8',
		],
	
		// // clean up the Url
		// 'urlManager' => [
			// 'enablePrettyUrl' => true,
			// 'showScriptName' => false,
		// ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
		//TO DO add dynamic IPs 
		'allowedIPs' => ['127.0.0.1', '::1', '192.168.*.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
		//Allow IPs to access gii: server, local host
		//TO DO add dynamic IPs 
		'allowedIPs' => ['127.0.0.1', '::1', '192.168.*.*'],
    ];
}

return $config;
