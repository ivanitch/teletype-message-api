<?php

/** @var array $params */

use yii\web\UrlManager;

return [
    'class'               => UrlManager::class,
    'hostInfo'            => $params['hostInfo'],
    'enablePrettyUrl'     => true,
    'enableStrictParsing' => true,
    'showScriptName'      => false,
    'rules'               => [
        ''                      => 'hello/index',
        'ping'                  => 'ping/index',

        // Добавление сообщений в http://neo-teletype.app (local)
        'POST api/v1/messages'  => 'v1/message/create',

        // Получение данных от https://api.teletype.app (remote)
        //'teletype-api/clients'  => 'notification/clients',
        //'teletype-api/channels' => 'notification/channels',
        //'teletype-api/dialogs'  => 'notification/dialogs',
        //'teletype-api/messages' => 'notification/messages',
    ]
];