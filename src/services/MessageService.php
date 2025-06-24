<?php

declare(strict_types=1);

namespace src\services;

use src\forms\MessageForm;
use src\interfaces\MessageFactoryInterface;
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
        private ClientRepository  $clientsRepository,
        private DialogRepository  $dialogsRepository,
        private MessageRepository $messagesRepository
    )
    {
    }

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
    public function process(MessageForm $form): null|MessageFactoryInterface
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Получаем или сохраняем Клиента
            $client = $this->getOrCreateClient($form);

            // Получаем или сохраняем Диалог
            $dialog = $this->getOrCreateDialog($client);

            // Сохраняем новое Сообщение если это НЕ дубликат
            $message = $this->getOrCreateMessage($client, $dialog, $form);
            if (!$message) {
                return null;
            }

            $transaction->commit();

            return $message;
        } catch (Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка при обработке сообщения: {$e->getMessage()}", __METHOD__);
            throw new Exception('Ошибка обработки сообщения', 0, $e);
        }
    }


    /**
     * Возвращает Клиента или сохраняет нового
     *
     * @param MessageForm $form
     *
     * @return Client
     *
     * @throws Exception
     */
    private function getOrCreateClient(MessageForm $form): MessageFactoryInterface
    {
        return $this->clientsRepository->make([
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
     *
     * @throws Exception
     */
    private function getOrCreateDialog(MessageFactoryInterface $client): MessageFactoryInterface
    {
        return $this->dialogsRepository->make([
            'client_id' => $client->id
        ]);
    }

    /**
     * Возвращает Сообщение Клиента в Диалоге (если оно уже есть) или сохраняет новое Сообщение
     *
     * @param Client $client
     * @param Dialog $dialog
     * @param MessageForm $form
     *
     * @return MessageFactoryInterface|null
     *
     * @throws Exception
     */
    private function getOrCreateMessage(
        Client      $client,
        Dialog      $dialog,
        MessageForm $form
    ): null|MessageFactoryInterface
    {
        if ($this->messagesRepository->exists($client, $form->external_message_id)) {
            return null;
        }

        return $this->messagesRepository->make([
            'external_client_id'  => $client->external_client_id,
            'dialog_id'           => $dialog->id,
            'external_message_id' => $form->external_message_id,
            'message_text'        => $form->message_text,
            'send_at'             => $form->send_at,
        ]);
    }
}
