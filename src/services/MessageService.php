<?php

declare(strict_types=1);

namespace api\services;

use api\forms\MessageForm;
use api\models\Client;
use api\models\Dialog;
use api\models\Message;
use Throwable;
use Yii;
use yii\db\Exception;

/**
 * Сервис обработки входящих сообщений из внешнего мессенджера.
 */
class MessageService
{
    /**
     * Ключевой метод сервиса, обрабатывает входящее сообщение.
     *
     * Для каждого клиента (определяемого `external_client_id`) создается уникальная запись в `clients`.
     * Каждый клиент имеет ровно один диалог (создается при первом сообщении).
     * Сообщения добавляются в диалог с проверкой уникальности `external_message_id`.
     * При добавлении уникального сообщения диалог обновляется.
     *
     * @param MessageForm $form
     *
     * @return null|Message
     *
     * @throws Throwable
     * @throws Exception
     */
    public function process(MessageForm $form): null|Message
    {

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Получаем или создаём клиента
            $client = $this->getOrCreateClient($form);

            // Получаем или создаём диалог
            $dialog = $this->getOrCreateDialog($client);

            // Проверяем сообщение на уникальность, следим за дубликатами
            if (Message::isDuplicate($client->external_client_id, $form->external_message_id,)) {
                $info = "Дубликат сообщения: external_message_id=$form->external_message_id,
                         external_client_id=$client->external_client_id";
                Yii::info($info);
                $transaction->rollBack();
                return null;
            }

            // Создаём сообщение
            $message = $this->createMessage($dialog, $client, $form);

            $transaction->commit();

            return $message;
        } catch (Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка при обработке сообщения: {$e->getMessage()}", __METHOD__);
            throw new Exception('Ошибка обработки сообщения', 0, $e);
        }
    }

    /**
     * Получает существующего клиента или создаёт нового.
     *
     * @param MessageForm $form
     * @return Client
     * @throws Exception
     */
    private function getOrCreateClient(MessageForm $form): Client
    {

        $client = Client::findOne(['external_client_id' => $form->external_client_id]);
        if ($client !== null) {
            return $client;
        }

        $client = new Client([
            'external_client_id' => $form->external_client_id,
            'client_phone'       => $form->client_phone,
        ]);

        if (!$client->save()) {
            throw new Exception('Ошибка при сохранении клиента: ' . json_encode($client->errors));
        }

        return $client;
    }

    /**
     * Возвращает диалог клиента или создаёт новый.
     *
     * @param Client $client
     *
     * @return Dialog
     *
     * @throws Exception
     */
    private function getOrCreateDialog(Client $client): Dialog
    {
        if ($client->dialog) {
            return $client->dialog;
        }

        $dialog = new Dialog(['client_id' => $client->id]);
        if (!$dialog->save()) {
            throw new Exception('Ошибка при создании диалога: ' . json_encode($dialog->errors));
        }

        return $dialog;
    }

    /**
     * Создаёт новое сообщение в диалоге.
     *
     * @param Dialog $dialog
     * @param Client $client
     * @param MessageForm $form
     * @return Message
     * @throws Exception
     */
    private function createMessage(Dialog $dialog, Client $client, MessageForm $form): Message
    {
        $message = new Message([
            'dialog_id'           => $dialog->id,
            'external_client_id'  => $client->external_client_id,
            'external_message_id' => $form->external_message_id,
            'message_text'        => $form->message_text,
            'send_at'             => $form->send_at,
        ]);

        if (!$message->save()) {
            throw new Exception('Ошибка при сохранении сообщения: ' . json_encode($message->errors));
        }

        // Триггер для обновления диалога
        $dialog->touch();

        return $message;
    }
}
