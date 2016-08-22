<?php
return array(
    'defaultController'=>'public', //if the game is standalone
    //'defaultController'=>'site', //if the game is standalone

    'components'=>array(
        'user'=>[
            'allowAutoLogin'=>true, //wline or ced.hu?
            ],
        'session' => array(
            'class' => 'system.web.CHttpSession',
            'autoStart' => true,
            'cookieMode' => 'only',
            'cookieParams' => array(
                'path' => '/',
                'domain' => 'ced.nrcode.local', //wline or ced.hu?
                'httpOnly' => true,
            ),
        ),

        'db'=>array(
            'connectionString' => 'mysql:host=localhost;dbname=ced.nrcode',
            'emulatePrepare' => true,
            'username' => 'root', //wline or ced.hu?
            'password' => 'root',
            'charset' => 'utf8',
            'enableProfiling' => true,
            'enableParamLogging' => true,
        ),
        'dbWline'=>array( //wline or ced.hu?
            'class' => 'CDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=wline',
            'emulatePrepare' => true,
            'username' => 'wline',
            'password' => '',
            'charset' => 'utf8',
            'enableProfiling' => true,
            'enableParamLogging' => true,
        ),
        "redis" => array(
            "class" => "vendor.codemix.yiiredis.ARedisConnection",
            "hostname" => "localhost",
            "port" => 6379,
            "database" => 1,
            "prefix" => "ced.nrcode:" //wline or ced.hu?
        ),

        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
                array(
                    'class'=>'vendor.malyshev.yii-debug-toolbar.YiiDebugToolbarRoute',
                    'ipFilters'=>array('127.0.0.1','192.168.*', '*'),
                ),
            ),
        ),
        'smtpmail'=>array(
            'class'=>'lib.smtpmail.PHPMailer',
            'Host'=>"in.mailjet.com",
            'Username'=>'c14f3e8951aebe00bc0afb81ec278d80',
            'Password'=>'86ee93cac5335f8bf5f08b0309971b8c',
            'Mailer'=>'smtp',
            'Port'=>587,
            'SMTPAuth'=>true,
        ),
    ),

    'params'=>require('params-local.php'),
);
