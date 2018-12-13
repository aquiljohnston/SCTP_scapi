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
//PG&E
	'pgeDevDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_PGE_GIS_DEV',
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
	'pgeProdDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.51;Database=vCAT_PGE_GIS_PROD',
		'username' => 'ApplicationCometTracker',
		'password' => '321cba',
		'charset' => 'utf8',
	],
//York
	'yorkDevDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database='.' vCAT_YORK_GIS_DEV',
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
	'yorkProdDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=vm-pa-ct2db01;Database=vCAT_YORK_GIS_PROD',
		'username' => 'ApplicationCometTracker',
		'password' => '6Iy2udrKO&Xy',
		'charset' => 'utf8',
	],
//Dominion
	'dominionStageDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=vCAT_DOMINION_GIS_STAGE',
		'username' => 'ApplicationCometTracker',
		'password' => '321cba',
		'charset' => 'utf8',
	],
	'dominionProdDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=vm-pa-ct2db01;Database=vCAT_DOMINION_GIS_PROD',
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
	'ctDemoDb' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'sqlsrv:Server=10.0.0.50;Database=CometTracker_Demo',
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

