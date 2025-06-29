<?php

declare(strict_types=1);

namespace src\validators;

use src\models\Message;
use src\repositories\ClientRepository;
use src\repositories\MessageRepository;
use yii\validators\Validator;

/**
 *  Проверяет, что для комбинации
 *  (`external_client_id` + `external_message_id` + `client_phone`) нет уже сохранённого сообщения.
 */
class MessageDuplicateValidator extends Validator
{
    public string $clientIdAttribute = 'external_client_id';
    public string $messageIdAttribute = 'external_message_id';
    public string $phoneAttribute = 'client_phone';
    private const string DUPLICATION_ERROR = 'Дубликат сообщения! clientID: %1$s, messageID: %2$s ';

    public function __construct(
        private readonly ClientRepository  $clientRepository,
        private readonly MessageRepository $messageRepository,
                                           $config = []
    )
    {
        parent::__construct($config);
    }

    public function validateAttribute($model, $attribute): void
    {
        /* @var Message $model */
        if ($model->hasErrors()) return;

        $externalClientId  = $model->{$this->clientIdAttribute};
        $clientPhone       = $model->{$this->phoneAttribute};
        $externalMessageId = $model->{$this->messageIdAttribute};

        if (!$this->clientRepository->exists([
            'external_client_id' => $externalClientId,
            'client_phone'       => $clientPhone
        ])) return;

        if ($this->messageRepository->exists(
            $externalClientId,
            $externalMessageId
        )) {
            $this->addError(
                $model,
                $attribute,
                sprintf(static::DUPLICATION_ERROR, $externalClientId, $externalMessageId));
        }
    }
}
