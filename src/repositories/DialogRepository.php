<?php

declare(strict_types=1);

namespace src\repositories;

use InvalidArgumentException;
use src\models\Dialog;


class DialogRepository extends AbstractRepository
{
    /**
     * @param array $params
     *
     * @return bool
     */
    public function exists(array $params): bool
    {
        return Dialog::find()->where($params)->exists();
    }

    /**
     * Поиск Диалога Клиента
     *
     * @param array $params
     *
     * @return Dialog|null
     */
    public function find(array $params): Dialog|null
    {
        return Dialog::findOne($params);
    }

    /**
     * Возвращает существующий Диалог или Сохраняет новый Диалог
     * Решение проблемы гонки за диалог с помощью Redis Mutex
     *
     * @param array $params
     *
     * @return Dialog
     */
    public function make(array $params): Dialog
    {
        $clientId = $params['client_id'] ?? null;

        if (!$clientId) {
            throw new InvalidArgumentException('Не передан `client_id`');
        }

        $dialog = $this->find($params);

        if (!$dialog) {
            $dialog = Dialog::create($params);
            $this->save($dialog, 'диалога');
        }

        return $dialog;
    }
}
