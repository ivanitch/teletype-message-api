<?php

declare(strict_types=1);

namespace api\forms;

use api\models\Message;
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
                ['external_client_id', 'external_message_id', 'client_phone', 'message_text', 'send_at'],
                'required'
            ],
            [
                ['external_message_id', 'external_client_id'],
                'string',
                'length' => 32
            ],
            ['client_phone', 'match', 'pattern' => '/^\+7\d{10}$/'],
            ['message_text', 'string', 'max' => 4096],
            ['send_at', 'integer'],
            ['external_message_id', 'validateMessageDuplicate'],
        ];
    }

    /**
     * Валидаторр
     * Проверяет, что для комбинации
     * (`external_client_id` + `external_message_id`) нет уже сохранённого сообщения.
     *
     * @return bool
     */
    public function validateMessageDuplicate(): bool
    {
        if ($this->hasErrors() || $this->messageExists()) {
            return false;
        }

        return true;
    }

    /**
     * Проверяет, что для комбинации `external_client_id` + `external_message_id` нет уже сохранённого сообщения.
     *
     * @return bool
     *
     */
    private function messageExists(): bool
    {
        return Message::isDuplicate(
            $this->external_client_id, $this->external_message_id
        );
    }
}
