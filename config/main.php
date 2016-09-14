<?php
return array(
    'basePath'=>'/data/www/ced.nrcode',
    'name'=>'Carp-e Diem',
    'defaultController'=>'site',

    // preloading 'log' component
    'preload'=>['log'],

    'aliases' => [
        'lib' => realpath(__DIR__ . '/../lib/'),
        'vendor' => realpath(__DIR__ . '/../lib/vendor/')
    ],

        // autoloading model and component classes
        'import'=>array(
            'application.models.*',
            'application.components.*',
            'vendor.codemix.YiiRedis.*',
        ),

        'sourceLanguage' => 'en_US',
        'language' => 'hu',

        'modules'=>[],

        'controllerMap'=>[
        'min'=>[
        'class'=>'vendor.limi7less.minscript.controllers.ExtMinScriptController',
        ],
        ],

        // application components
        'components'=>array(
            'clientScript'=>[
            'class'=>'vendor.limi7less.minscript.components.ExtMinScript',
            ],
            'user'=>[
            'allowAutoLogin'=>false,
            ],
            'player' => [
            'class'=> 'application.components.PlayerComponent',
            ],
            'gameLogger' => [
            'class'=> 'application.components.LoggerComponent',
            ],
            'badge' => [
            'class'=> 'application.components.BadgeComponent',
            ],
            'session' => array (
                'class' => 'system.web.CHttpSession',
                'autoStart' => false,
                'cookieMode' => 'only',
                'cookieParams' => array(
                    'path' => '/',
                    'domain' => '.wline.hu',
                    'httpOnly' => true,
                ),
            ),

            // uncomment the following to enable URLs in path-format
            'urlManager'=>array(
                'urlFormat'=>'path',
                'showScriptName'=>false,
                'caseSensitive'=>false,
                'rules'=>array(
                    '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                    '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                    '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
                ),
            ),
            'db'=>array(
                'connectionString' => 'mysql:host=localhost;dbname=ced',
                'emulatePrepare' => true,
                'username' => 'user',
                'password' => 'password',
                'charset' => 'utf8',
            ),
            'dbWline'=>array(
                'class' => 'CDbConnection',
                'connectionString' => 'mysql:host=localhost;dbname=wline',
                'emulatePrepare' => true,
                'username' => 'user',
                'password' => 'password',
                'charset' => 'utf8',
            ),
            "redis" => array(
                "class" => "vendor.codemix.yiiredis.ARedisConnection",
                "hostname" => "localhost",
                "port" => 6379,
                "database" => 1,
                "prefix" => "fish:"
            ),

            'errorHandler'=>array(
                // use 'site/error' action to display errors
                'errorAction'=>'/error',
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
            'cache'=>[
            'class'=>'system.caching.CMemCache',
            'servers'=>[
                ['host'=>'localhost', 'port'=>11211],
            ],
            ],
            'smtpmail'=>array(
                'class'=>'vendor.smtpmail.PHPMailer',
                'Host'=>"mail.yourdomain.com",
                'Username'=>'test@yourdomain.com',
                'Password'=>'test',
                'Mailer'=>'smtp',
                'Port'=>26,
                'SMTPAuth'=>true,
            ),
        ),

        'params'=>require('params.php'),
    );
