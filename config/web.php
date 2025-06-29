<?php

use src\services\SafeMutexService;
use yii\caching\FileCache;
use yii\log\FileTarget;
use yii\redis\Connection;
use yii\redis\Mutex;
use yii\web\JsonParser;

$params     = require __DIR__ . '/params.php';
$db         = require __DIR__ . '/db.php';
$urlManager = require __DIR__ . '/urlManager.php';

$config = [
    'id'                  => 'teletype-app',
    'basePath'            => dirname(__DIR__),
    'homeUrl'             => '/',
    'defaultRoute'        => 'hello/index',
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'src\controllers',
    'components'          => [
        'request'    => [
            'cookieValidationKey' => 'yA5FATSDaF1g1WwPiycAI1Y_9qJO7cJj',
            'parsers'             => [
                'application/json' => JsonParser::class,
            ],
        ],
        'user'       => [
            'identityClass'   => false,
            'enableAutoLogin' => false,
            'enableSession'   => false,
        ],
        'cache'      => [
            'class' => FileCache::class,
        ],
        'log'        => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => FileTarget::class,
                    'levels' => ['error', 'warning', 'info'],
                    // Исключаем ненужные сообщения. На уровне `info` записывваем дубликаты сообщения
                    'except' => [
                        'yii\filters\RateLimiter::beforeAction',
                        'yii\db\Connection::open',
                        'yii\db\Command::query',
                        'yii\db\Command::execute',
                    ],
                ],
            ],
        ],
        'db'         => $db,
        'urlManager' => $urlManager,
        'redis'      => [
            'class'    => Connection::class,
            'hostname' => 'teletype_redis',
            'port'     => 6379,
            'database' => 0,
        ],
        'safeMutex'  => function () {
            $mutex        = Yii::createObject(Mutex::class);
            $mutex->redis = Yii::$app->redis;
            return new SafeMutexService($mutex);
        },
    ],
    'params'              => $params,
];

return $config;
