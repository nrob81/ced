<?php
defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL') || define('YII_TRACE_LEVEL', 3);

// change the following paths if necessary
require(dirname(__FILE__) . '/../../../yii/framework.1.1.16/yii.php');
$base = require(dirname(__FILE__) . '/../config/main.php');
$local = require(dirname(__FILE__) . '/../config/main-local.php');
$config = CMap::mergeArray($base, $local);

require(dirname(__FILE__) . '/../lib/vendor/autoload.php');
Yii::createWebApplication($config)->run();
