<?php

declare(strict_types=1);

namespace api\controllers;

class HelloController extends BaseRestController
{
    public function actionIndex(): string
    {
        return 'Hello world! 👋 | Yii version ' . \Yii::getVersion();
    }
}
