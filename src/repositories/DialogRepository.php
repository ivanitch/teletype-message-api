<?php

declare(strict_types=1);

namespace src\repositories;

use InvalidArgumentException;
use RuntimeException;
use src\interfaces\MessageFactoryInterface;
use src\models\Dialog;
use src\services\RedisService;

class DialogRepository extends AbstractRepository
{
    /**
     * Поиск Диалога Клиента
     *
     * @param array $params
     *
     * @return Dialog|null
     */
    public function find(array $params): MessageFactoryInterface|null
    {
        return Dialog::findOne($params);
    }


    /**
     * Возвращает существующий Диалог или Сохраняет новый Диалог
     * Используем Redis для решения проблемы "Гонка за диалог"
     *
     * @param array $params
     *
     * @return MessageFactoryInterface
     */
    public function make(array $params): MessageFactoryInterface
    {
        $clientId = $params['client_id'] ?? null;

        if (!$clientId) {
            throw new InvalidArgumentException('Не передан client_id');
        }

        $key = "dialog_lock:client_$clientId";
        $ttl = 3;

        $lockAcquired = RedisService::set($key, 1, $ttl);

        if (!$lockAcquired) {
            throw new RuntimeException("Диалог с клиентом #$clientId уже обрабатывается (lock)");
        }

        try {
            $dialog = $this->find($params) ?? Dialog::create($params);

            if ($dialog->isNewRecord) {
                $this->save($dialog, 'диалога');
            }

            return $dialog;
        } finally {
            RedisService::destroy($key);
        }
    }

}
