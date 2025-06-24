<?php

declare(strict_types=1);

namespace src\forms;

use src\validators\MessageDuplicateValidator;
use src\validators\PhoneValidator;
use yii\base\Model;

/**
 * @property string $external_client_id Уникальный идентификатор клиента
 * @property string $external_message_id Уникальный идентификатор сообщения
 * @property string $client_phone Уникальный номер телефона клиента
 * @property string $message_text Текст сообщения
 * @property integer $send_at Дата-время отправки сообщения
 */
class MessageForm extends Model
{
    public ?string $external_client_id = null;
    public ?string $external_message_id = null;
    public ?string $client_phone = null;
    public ?string $message_text = null;
    public ?int $send_at = null;

    public function rules(): array
    {
        return [
            [
                [
                    'external_client_id',
                    'external_message_id',
                    'client_phone',
                    'message_text',
                    'send_at'
                ],
                'required'
            ],
            ['send_at', 'integer'],
            ['message_text', 'string', 'max' => 4096],
            [
                ['external_message_id', 'external_client_id'],
                'string',
                'length' => 32
            ],
            ['client_phone', PhoneValidator::class],
            [
                'external_message_id',
                MessageDuplicateValidator::class,
                'clientIdAttribute' => 'external_client_id',
            ],
        ];
    }
}
