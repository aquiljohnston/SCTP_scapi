<?php

return [
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
	'yorkDevDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=ScctTemplate',
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
	'yorkStageDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_YORK_GIS_STAGE',
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
	'yorkProdDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.51;Database=vCAT_YORK_GIS_PROD',
		'username' => 'ApplicationCometTracker',
		'password' => '321cba',
		'charset' => 'utf8',
	],
	'demoDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_DEMO_GIS_DEV',
		'username' => 'ApplicationCometTracker',
		'password' => '321cba',
		'charset' => 'utf8',
	],
];

