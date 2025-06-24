<?php

declare(strict_types=1);

namespace src\controllers;

class HelloController extends BaseRestController
{
    public function actionIndex(): string
    {
        return 'Hello world! 👋 | Yii version ' . \Yii::getVersion();
    }

    public function actionTest()
    {
        \Yii::$app->redis->set('test_key', 'Hello, Redis!');
        $value = \Yii::$app->redis->get('test_key');


        print_r(\Yii::$app->redis);

        return $value; // Должно вернуть "Hello, Redis!"
    }
}
