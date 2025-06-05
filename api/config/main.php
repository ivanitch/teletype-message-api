<?php

return [
    'id'                  => 'teletype-message-api',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'language'            => 'ru',
    'sourceLanguage'      => 'ru-RU',
    'bootstrap'           => [
        'log'
    ],
    'components'          => [
        'request'    => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response'   => [
            'formatters' => [
                'json' => [
                    'class'         => 'yii\web\JsonResponseFormatter',
                    'prettyPrint'   => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'log'        => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => require __DIR__ . '/routes.php'
    ],
];
