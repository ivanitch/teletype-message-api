<?php

declare(strict_types=1);

namespace api\controllers;

class MessageController extends BaseRestController
{
    public function actionIndex(): string
    {
        return 'PHP_VERSION: ' . PHP_VERSION;
    }
}
