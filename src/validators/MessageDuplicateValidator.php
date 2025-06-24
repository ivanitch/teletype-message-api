<?php

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

    public $message = 'Дубликат сообщения!';

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
        if ($model->hasErrors()) {
            return;
        }

        $externalClientId  = $model->{$this->clientIdAttribute};
        $clientPhone       = $model->{$this->phoneAttribute};
        $externalMessageId = $model->{$this->messageIdAttribute};

        $client = $this->clientRepository->find([
            'external_client_id' => $externalClientId,
            'client_phone'       => $clientPhone,
        ]);

        if (!$client) {
            return;
        }

        if ($this->messageRepository->exists($client, $externalMessageId)) {
            $this->addError($model, $attribute, $this->message);
        }
    }
}
