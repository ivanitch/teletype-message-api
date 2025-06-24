<?php

namespace src\services;

use Yii;
use yii\redis\Connection;

class RedisLockService
{
    public static function set(string $key, int $ttl = 3): bool
    {
        return Yii::$app->redis->set($key, 1, 'NX', 'EX', $ttl);
    }

    public static function get(string $key): bool
    {
        return (bool) Yii::$app->redis->get($key);
    }

    public static function destroy(string $key): void
    {
        Yii::$app->redis->del($key);
    }
}
