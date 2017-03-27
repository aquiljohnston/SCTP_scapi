<?php

use app\authentication\CTUser;

$params = require(__DIR__ . '/params.php');
set_time_limit(180); // increases max exec time
$config = [
    'id' => 'scapi',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
	//module configs for versioning
	'modules' => [
		'v1' => [
			'class' => 'app\modules\v1\Module',
		],
        'v2' => [
            'class' => 'app\modules\v2\Module'
        ]
    ],
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
            'identityClass' => 'app\modules\v1\models\SCUser',
            'enableAutoLogin' => false,
			'authTimeout' => 28800,
			//'authTimeout' => 15,
			'loginUrl' => null
        ],
		'authManager' => [
            'class' => 'app\rbac\ScDbManager',
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
		'betaDb' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'sqlsrv:Server=10.0.0.50;Database=ScctTemplate',
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

    // allows overriding config settings on a development environment
    if (file_exists(__DIR__.'/dev_config_override.php')) {
        $devConfigOverride = require(__DIR__.'/dev_config_override.php');
        if (is_array($devConfigOverride)) {
            $config = array_replace_recursive($config, $devConfigOverride);
        }
    }
}

return $config;
