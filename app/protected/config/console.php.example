<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',

	// preloading 'log' component
	'preload'=>array('log'),

	// application components
	'components'=>array(
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=egoweb',
			'username' => 'egowebuser',
			'password' => "egowebpass",
			//'enableProfiling'=>true,
			'emulatePrepare' => true,
			'charset' => 'utf8',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),

		'securityManager'=>array(
			'cryptAlgorithm' => 'blowfish',
			'encryptionKey' => 'One morning I shot an elephant in my pajamas.',
		)
	),
);
