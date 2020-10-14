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
    ],
    'params' => $params,
];
