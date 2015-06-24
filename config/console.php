<?php
defined('LOG_CATEGORY') or define('LOG_CATEGORY', 'zoho');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'zoho' => [
            'class' => 'app\components\Zoho',
            'authUrl' => 'https://accounts.zoho.com/apiauthtoken/nb/create',
            'baseUrl' => 'https://invoice.zoho.com/api/v3'
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'class' => 'app\components\ZohoError'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                    'logFile' => '@runtime/logs/account.log',
                    'categories' => ['account'],
                    'logVars' => []
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                    'logFile' => '@runtime/logs/zoho.log',
                    'categories' => [LOG_CATEGORY],
                    'logVars' => []
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'mailer' => 'mailer',
                    'levels' => ['error', 'warning'],
                    'message' => [
                        'from' => ['noreply@zoho.parser.com'],
                        'to' => ['dmitrij.zajats@gmail.com'],
                        'subject' => 'Zoho parser!!!',
                    ],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
];
