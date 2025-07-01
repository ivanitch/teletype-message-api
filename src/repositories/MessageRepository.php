<?php

declare(strict_types=1);

namespace src\repositories;

use src\models\Message;
use yii\db\Exception;

class MessageRepository extends AbstractRepository
{
    /**
     * Проверка на наличие Сообщения Клиента (Дубликат)
     *
     * @param array $params
     *
     * @return bool
     */
    public function exists(array $params): bool
    {
        return Message::find()->where($params)->exists();
    }

    /**
     * Поиск Сообщения по (`external_client_id` + `external_message_id`)
     *
     * @param array $params
     *
     * @return Message|null
     */
    public function find(array $params): Message|null
    {
        return Message::findOne([
            'external_client_id'  => $params['external_client_id'],
            'external_message_id' => $params['external_message_id'],
        ]);
    }

    /**
     * Возвращает Сообщение из БД или Сохраняет новое Сообщение
     *
     * @param array $params
     *
     * @return Message
     *
     * @throws Exception
     */
    public function make(array $params): Message
    {
        $message = $this->find($params);

        if (!$message) {
            $message = Message::create($params);
            $this->save($message, 'сообщения');
            $message->dialog->touchUpdateAt();
        }

        return $message;
    }
}
