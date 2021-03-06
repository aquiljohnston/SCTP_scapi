<?php
//"I promise you it's gonna work." - Andre 08/01/17
use app\authentication\CTUser;

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');
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
            'class' => 'app\modules\v2\Module',
        ],
		'v3' => [
            'class' => 'app\modules\v3\Module',
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
		//these setting can be overwritten within the module config
        'user' => [
			'class' => 'app\authentication\CTUser',
            'identityClass' => 'app\modules\v1\models\SCUser',
            'enableAutoLogin' => false,
			'authTimeout' => 43200,
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
			'useFileTransport'=>false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.office365.com',
                'username' => 'SC_Automated_Reporting@southerncrossinc.com',
                'password' => '~!3175Scc30071',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','trace'],
                    'except' => [
                        'yii\web\HttpException:401',
                        'yii\web\HttpException:403'
                    ],
                ],
            ],
        ],
		//database connections
		//Comet Tracker
        'ctDevDb' => $db['ctDevDb'],
		'ctStageDb' => $db['ctStageDb'],
		'ctProdDb' => $db['ctProdDb'],
		//Scana
		'scanaDevDb' => $db['scanaDevDb'],
		'scanaStageDb' => $db['scanaStageDb'],
		'scanaProdDb' => $db['scanaProdDb'],
		//Demo
        'demoDb' => $db['demoDb'],
		// // clean up the Url
		// 'urlManager' => [
			// 'enablePrettyUrl' => true,
			// 'showScriptName' => false,
		// ],
		'consoleRunner' => [
			'class' => 'toriphes\console\Runner'
		],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    // $config['bootstrap'][] = 'debug';
    // $config['modules']['debug'] = [
        // 'class' => 'yii\debug\Module',
		// //TO DO add dynamic IPs
		// 'allowedIPs' => ['127.0.0.1', '::1', '192.168.*.*'],
    // ];

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
