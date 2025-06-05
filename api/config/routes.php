<?php

use yii\web\UrlManager;

return [
    'class'               => UrlManager::class,
    'enablePrettyUrl'     => true,
    'enableStrictParsing' => true,
    'showScriptName'      => false,
    'cache'               => false,
    'rules'               => [
        '' => 'message/index',
    ],
];
