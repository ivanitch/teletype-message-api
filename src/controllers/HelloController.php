<?php

declare(strict_types=1);

namespace api\controllers;

use yii\web\Controller;

class HelloController extends Controller
{
    public function actionIndex(): string
    {
        return 'Hello world! 👋';
    }
}
