<?php

/** @var array<string, string> $params */

return [
    'class'               => \yii\web\UrlManager::class,
    'hostInfo'            => $params['hostInfo'],
    'enablePrettyUrl'     => true,
    'enableStrictParsing' => true,
    'showScriptName'      => false,
    'rules'               => [
        '' => 'hello/index',
    ]
];