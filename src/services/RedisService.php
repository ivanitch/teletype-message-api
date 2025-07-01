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
     * Атомарно устанавливает значение, если его ещё нет.
     * Применяется для идемпотентности или блокировки.
     *
     * @param string $key
     * @param int|string $value
     * @param int $ttl Время жизни в секундах
     *
     * @return bool Успешно ли установлен ключ
     */
    public function setIfNotExists(string $key, int|string $value = 1, int $ttl = 60): bool
    {
        return self::getConnection()->set($key, $value, 'NX', 'EX', $ttl) === true;
    }

    /**
     * Принудительно устанавливает значение с TTL
     *
     * @param string $key
     * @param int $ttl
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWithTtl(string $key, int $ttl, mixed $value): void
    {
        self::getConnection()->executeCommand('SETEX', [$key, $ttl, $value]);
    }

    /**
     * Проверяет, существует ли ключ
     *
     * @param string $key
     *
     * @return bool
     *
     * @throws Exception
     */
    public function exists(string $key): bool
    {
        return (bool)self::getConnection()->executeCommand('EXISTS', [$key]);
    }

    /**
     * Удаляет ключ
     *
     * @param string $key
     */
    public function delete(string $key): void
    {
        self::getConnection()->del($key);
    }

    /**
     * Получает значение ключа
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return self::getConnection()->get($key);
    }

    /**
     * Возвращает соединение Redis
     *
     * @return Connection
     */
    public static function getConnection(): Connection
    {
        return self::$connection ??= Yii::$app->redis;
    }
}
