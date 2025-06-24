<?php

declare(strict_types=1);

namespace src\repositories;

use InvalidArgumentException;
use RuntimeException;
use src\interfaces\MessageFactoryInterface;
use src\models\Dialog;
use src\services\RedisLockService;

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

        $lockKey = "dialog_lock:client_$clientId";

        if (!RedisLockService::set($lockKey)) {
            throw new RuntimeException("Диалог с клиентом #$clientId обрабатывается другим процессом");
        }

        try {
            $dialog = $this->find($params) ?? Dialog::create($params);

            if ($dialog->isNewRecord) {
                $this->save($dialog, 'диалога');
            }

            return $dialog;
        } finally {
            RedisLockService::destroy($lockKey);
        }
    }
}
