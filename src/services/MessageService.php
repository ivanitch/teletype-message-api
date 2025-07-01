<?php

declare(strict_types=1);

namespace src\services;

use src\forms\MessageForm;
use src\models\{Client, Dialog, Message};
use src\repositories\{ClientRepository, DialogRepository, MessageRepository};
use Throwable;
use Yii;
use yii\db\Exception;

/**
 * Сервис обработки входящих сообщений из внешних мессенджеров.
 */
readonly class MessageService
{
    public function __construct(
        private SafeMutexService  $mutex,
        private RedisService      $redis,
        private ClientRepository  $clients,
        private DialogRepository  $dialogs,
        private MessageRepository $messages,
    )
    {
    }

    /**
     * Ключевой метод сервиса, обрабатывает входящее сообщение.
     *
     * Для каждого клиента (определяемого `external_client_id` + `client_phone`) создается уникальная запись в `clients`.
     * Каждый клиент имеет ровно один диалог (создается при первом сообщении).
     * Сообщения добавляются в диалог с проверкой уникальности (`dialog_id` + `external_client_id` + `external_message_id`).
     * При добавлении сообщения диалог обновляется.
     *
     * @param MessageForm $form
     *
     * @return Message|null
     *
     * @throws Throwable
     * @throws Exception
     */
    public function process(MessageForm $form): Message|null
    {
        $clientId      = $form->external_client_id;
        $messageId     = $form->external_message_id;
        $idempotentKey = "idempotent:msg:$clientId:$messageId";
        $mutexKey      = "lock:client:$clientId";

        if (!$this->redis->setIfNotExists($idempotentKey, 1, 3600)) {
            Yii::info("Дубликат! Клиент: $clientId, Сообщение: $messageId, Redis key: $idempotentKey");
            return null;
        }

        return $this->mutex->run($mutexKey, function () use ($form, $idempotentKey, $clientId, $messageId) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $client  = $this->getOrCreateClient($form);
                $dialog  = $this->getOrCreateDialog($client);
                $message = $this->getOrCreateMessage($client, $dialog, $form);

                if (!$message) {
                    return null;
                }

                $transaction->commit();

                return $message;
            } catch (Throwable $e) {
                $transaction->rollBack();
                $this->redis->delete($idempotentKey); // Откат идемпотентного ключа
                Yii::error("Ошибка при обработке сообщения: {$e->getMessage()}", __METHOD__);
                throw new Exception('Ошибка обработки сообщения', 0, $e);
            }
        });
    }


    /**
     * Возвращает Клиента или сохраняет нового
     *
     * @param MessageForm $form
     *
     * @return Client
     */
    private function getOrCreateClient(MessageForm $form): Client
    {
        return $this->clients->make([
            'external_client_id' => $form->external_client_id,
            'client_phone'       => $form->client_phone,
        ]);
    }

    /**
     * Возвращает Диалог Клиента или создаёт новый.
     *
     * @param Client $client
     *
     * @return Dialog
     */
    private function getOrCreateDialog(Client $client): Dialog
    {
        return $this->dialogs->make([
            'client_id' => $client->id
        ]);
    }

    /**
     * Возвращает Сообщение Клиента в Диалоге (если оно уже есть) или сохраняет новое
     *
     * @param Client $client
     * @param Dialog $dialog
     * @param MessageForm $form
     *
     * @return Message|null
     *
     * @throws Exception
     */
    private function getOrCreateMessage(
        Client      $client,
        Dialog      $dialog,
        MessageForm $form
    ): Message|null
    {
        $clientId  = $client->external_client_id;
        $messageId = $form->external_message_id;

        $params = [
            'external_client_id'  => $clientId,
            'external_message_id' => $messageId,
        ];

        if ($this->messages->exists($params)) {
            return null;
        }

        return $this->messages->make([
            'external_client_id'  => $clientId,
            'dialog_id'           => $dialog->id,
            'external_message_id' => $messageId,
            'message_text'        => $form->message_text,
            'send_at'             => $form->send_at,
        ]);
    }
}
