<?php

use yii\caching\FileCache;
use yii\log\FileTarget;
use yii\web\JsonParser;

$params     = require __DIR__ . '/params.php';
$db         = require __DIR__ . '/db.php';
$urlManager = require __DIR__ . '/urlManager.php';

$config = [
    'id'         => 'teletype-app',
    'basePath'   => dirname(__DIR__),
    'homeUrl' => '/',
    'defaultRoute' => 'main/index',
    'bootstrap'  => ['log'],
    'controllerNamespace' => 'api\controllers',
    'components' => [
        'request'    => [
            'cookieValidationKey' => 'yA5FATSDaF1g1WwPiycAI1Y_9qJO7cJj',
            'parsers' => [
                'application/json' => JsonParser::class,
            ],
        ],
        'cache'      => [
            'class' => FileCache::class,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
        'db'         => $db,
        'urlManager' => $urlManager,
    ],
    'params'     => $params,
];

return $config;
