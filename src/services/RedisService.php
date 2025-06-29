<?php

declare(strict_types=1);

namespace src\services;

use Yii;
use yii\db\Exception;
use yii\redis\Connection;

class RedisService
{
    private static ?Connection $connection = null;

    /**
     * Устанавливает ключ, если он ещё не установлен (атомарная блокировка)
     *
     * @param string $key Ключ блокировки
     * @param int|string $value Значение блокировки
     * @param int $ttl Время жизни в секундах
     *
     * @return bool             Успешно ли установлена блокировка
     */
    public static function set(string $key, int|string $value = 1, int $ttl = 3): bool
    {
        // NX — установить только если ключ не существует
        // EX — время жизни в секундах
        return self::getConnection()->set($key, $value, 'NX', 'EX', $ttl) === true;
    }

    /**
     * @param string $key
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function exists(string $key): bool
    {
        return (bool)self::getConnection()->executeCommand('EXISTS', [$key]);
    }

    /**
     * @param string $key
     * @param int $ttl
     * @param $value
     *
     * @return void
     *
     * @throws Exception
     */
    public static function setex(string $key, int $ttl, $value): void
    {
        self::getConnection()->executeCommand('SETEX', [$key, $ttl, $value]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        return self::getConnection()->get($key);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public static function destroy(string $key): void
    {
        self::getConnection()->del($key);
    }

    /**
     * @return Connection
     */
    public static function getConnection(): Connection
    {
        if (self::$connection === null) {
            self::$connection = Yii::$app->redis;
        }

        return self::$connection;
    }
}

