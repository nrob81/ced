<?php
defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

// change the following paths if necessary
require(dirname(__FILE__) . '/../../yii/framework/yii.php');
$base = require(dirname(__FILE__) . '/../config/main.php');
$local = require(dirname(__FILE__) . '/../config/main-local.php');
$config = CMap::mergeArray($base, $local);

Yii::createWebApplication($config)->run();
