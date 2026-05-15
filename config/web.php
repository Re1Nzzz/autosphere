<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '123',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl'        => ['/auth/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                ''                          => 'site/index',
                'login'                     => 'auth/login',
                'logout'                    => 'auth/logout',
                'register'                  => 'auth/register',
                'constructor'               => 'constructor/index',
                'constructor/save'          => 'constructor/save',
                'gallery'                   => 'gallery/index',
                'gallery/<id:\d+>'          => 'gallery/view',
                'gallery/like/<id:\d+>'     => 'gallery/like',
                'gallery/comment/<id:\d+>'  => 'gallery/comment',
                'garage'                    => 'garage/index',
                'garage/delete/<id:\d+>'    => 'garage/delete',
                'garage/publish/<id:\d+>'   => 'garage/publish',
                'map'                       => 'map/index',
                'map/add'                   => 'map/add',
                'map/data'                  => 'map/data',
                'map/delete/<id:\d+>'       => 'map/delete',
                'chat'                      => 'chat/index',
                'chat/send'                 => 'chat/send',
                'chat/poll'                 => 'chat/poll',
                'chat/delete/<id:\d+>'      => 'chat/delete',
                'chat/ban/<id:\d+>'         => 'chat/ban',
                'contact'                   => 'site/contact',
                'admin'                     => 'admin/index',
                'admin/parts'               => 'admin/parts',
                'admin/part-save'           => 'admin/partSave',
                'admin/part-delete/<id:\d+>'=> 'admin/partDelete',
                'admin/users'               => 'admin/users',
                'admin/moderate'            => 'admin/moderate',
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
