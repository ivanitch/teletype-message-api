<?php

declare(strict_types=1);

namespace src\repositories;

use InvalidArgumentException;
use RuntimeException;
use src\interfaces\MessageFactoryInterface;
use src\models\Dialog;
use src\services\RedisService;
use yii\redis\Mutex;

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
     * Решение проблемы гонки за диалог с помощью Redis Mutex
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

        $mutex = new Mutex(new RedisService());
        $lockKey = "dialog_lock:client_$clientId";
        $lockTimeout = 3;

        if (!$mutex->acquire($lockKey, $lockTimeout)) {
            throw new RuntimeException("Диалог с клиентом #$clientId уже обрабатывается (lock)");
        }

        try {
            $dialog = $this->find($params);

            if ($dialog === null) {
                $dialog = Dialog::create($params);

                if ($dialog->isNewRecord) {
                    $this->save($dialog, 'диалога');
                }
            }

            return $dialog;
        } finally {
            $mutex->release($lockKey);
        }
    }
}
