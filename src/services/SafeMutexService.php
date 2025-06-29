<?php

namespace src\services;

use Yii;
use yii\redis\Mutex;

readonly class SafeMutexService
{
    public function __construct(private Mutex $mutex)
    {
    }

    public function run(string $key, callable $callback, int $timeout = 10)
    {
        if (!$this->mutex->acquire($key, $timeout)) {
            Yii::warning("Не удалось захватить мьютекс: $key", __METHOD__);
            return null;
        }

        try {
            return $callback();
        } finally {
            $this->mutex->release($key);
        }
    }
}
