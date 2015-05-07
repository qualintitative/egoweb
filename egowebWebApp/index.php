<?php
ini_set('memory_limit','3072M');
error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);

// change the following paths if necessary
$yii=dirname(__FILE__).'/system/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';
$custy=dirname(__FILE__).'/protected/extensions/custy.php';

// custom functions
require_once($custy);

// remove the following lines when in production mode
//defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
//defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',2);

require_once($yii);
Yii::setPathOfAlias('plugins', dirname(__FILE__).'/plugins/');
Yii::createWebApplication($config)->run();
