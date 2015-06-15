<?php
// change the following paths if necessary
require(dirname(__FILE__) . '/../../yii/framework/yiit.php');
$test = require(dirname(__FILE__) . '/../config/test.php');
$local = require(dirname(__FILE__) . '/../config/test-local.php');
$config = CMap::mergeArray($test, $local);

require_once(dirname(__FILE__).'/WebTestCase.php');
Yii::createWebApplication($config);
