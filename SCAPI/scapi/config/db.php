<?php

return [
//Comet Tracker
	'ctDevDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=CometTracker',
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
	'ctProdDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=vm-pa-ct2db;Database=AZCometTracker_PROD',
		'username' => 'ApplicationCometTracker',
		'password' => '6Iy2udrKO&Xy',
		'charset' => 'utf8',
	],
//Scana
	'scanaDevDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_SCANA_GIS_DEV',
		'username' => 'ApplicationCometTracker',
		'password' => '321cba',
		'charset' => 'utf8',
	],
	'scanaStageDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_SCANA_GIS_STAGE',
		'username' => 'ApplicationCometTracker',
		'password' => '321cba',
		'charset' => 'utf8',
	],
	'scanaProdDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=vm-pa-ct2dbscan;Database=vCAT_SCANA_GIS_PROD',
		'username' => 'ApplicationCometTracker',
		'password' => '6Iy2udrKO&Xy',
		'charset' => 'utf8',
	],
//Demo
	'demoDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_DEMO_GIS_DEV',
		'username' => 'ApplicationCometTracker',
		'password' => '321cba',
		'charset' => 'utf8',
	],
];

