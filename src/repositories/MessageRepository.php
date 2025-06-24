<?php

declare(strict_types=1);

namespace src\repositories;

use src\interfaces\MessageFactoryInterface;
use src\models\{Client, Dialog, Message};
use Yii;
use yii\db\{Exception, Expression};

class MessageRepository extends AbstractRepository
{
    private const string IS_DUPLICATED_MESSAGE = 'Дубликат сообщения: %1$s, клиента: %2$s';


    /**
     * Поиск Сообщения по (`external_client_id` + `external_message_id`)
     *
     * @param array $params
     *
     * @return Message|null
     */
    public function find(array $params): MessageFactoryInterface|null
    {
        return Message::findOne([
            'external_client_id'  => $params['external_client_id'],
            'external_message_id' => $params['external_message_id'],
        ]);
    }

    /**
     * Проверка на наличие Сообщения Клиента (Дубликат)
     * Дубликаты записываем в лог
     *
     * @param Client $client
     * @param string $messageId
     *
     * @return bool
     */
    public function exists(Client $client, string $messageId): bool
    {
        $exists = Message::find()
            ->where([
                'external_client_id'  => $client->external_client_id,
                'external_message_id' => $messageId,
            ])
            ->exists();

        if ($exists) {
            Yii::info(sprintf(
                static::IS_DUPLICATED_MESSAGE,
                "external_message_id=$messageId",
                "external_client_id=$client->external_client_id"
            ));
            return true;
        }

        return false;
    }

    /**
     * Возвращает Сообщение, которое уже есть в БД или Сохраняет новое Сообщение
     *
     * @param array $params
     *
     * @return MessageFactoryInterface
     *
     * @throws Exception
     */
    public function make(array $params): MessageFactoryInterface
    {
        $message = $this->find($params) ?? Message::create($params);;

        if ($message->isNewRecord) {
            $this->save($message, 'сообщения');
            static::touchUpdateAtDialog($message->dialog_id);
        }

        return $message;
    }

    /**
     * Триггерим обновление `updated_at` через "пустой" UPDATE в диалоге ТОЛЬКО при новом сообщении
     *
     * @param int $dialogId ID Диалога, в который добавляется Сообщение
     *
     * @return void
     *
     * @throws Exception
     */
    private static function touchUpdateAtDialog(int $dialogId): void
    {
        Yii::$app->db->createCommand()
            ->update(
                Dialog::tableName(),
                ['updated_at' => new Expression('updated_at')], ['id' => $dialogId]
            )
            ->execute();
    }
}
