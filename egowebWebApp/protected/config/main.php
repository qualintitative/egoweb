<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'EgoWeb 2.0',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.yiidebugtb.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool

		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'12345',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1',"*"),
		),

	),

	// application components
	'components'=>array(
        'user'=>array(
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
            'class'=>'WebUser'
        ),
		// uncomment the following to enable URLs in path-format

		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'gii/default/login'=>'gii/default/login',
				'<controller:\w+>/<action:\w+>/<id:(.*)+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',

			),
			'showScriptName'=>false,
		),
		/*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		*/
		'db'=>array(
			/*'connectionString' => 'mysql:host=localhost;dbname=egoweb_data',
			'username' => 'egoweb',
			'password' => "test1234",*/
			'connectionString' => 'mysql:host=localhost;dbname=egoweb_data',
			'username' => 'egoweb',
			'password' => "test1234",
			'emulatePrepare' => true,
			'charset' => 'utf8',
		),

		'session' => array (
    		'class' => 'system.web.CDbHttpSession',
    		'connectionID' => 'db',
    		'sessionTableName' => 'session',
		),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning, trace',
				),
				array( // configuration for the toolbar
					'class'=>'XWebDebugRouter',
					'config'=>'alignLeft, opaque, fixedPos, collapsed, yamlStyle',
					'levels'=>'error, warning, trace, profile, info',
					'allowedIPs'=>array('127.0.0.1','::1','localhost'),
				),
			),

		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'info@bluscs.com',
	),
);