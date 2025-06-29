<?php

declare(strict_types=1);

namespace src\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Сообщение
 *
 * @property integer $id                 ID записи в таблице
 * @property integer $dialog_id          ID диалога
 * @property string $external_client_id  Уникальный идентификатор клиента
 * @property string $external_message_id Уникальный идентификатор сообщения
 * @property string $message_text        Текст сообщения
 * @property integer $send_at            Дата-время отправки в формате `unixtime`
 * @property integer $created_at         Дата-время создания сообщения
 *
 * @property-read Dialog $dialog Связь с диалогом клиента
 * @property-read Client $client Связь с клиентом
 */
class Message extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%messages}}';
    }

    public function rules(): array
    {
        return [
            [['dialog_id', 'external_client_id', 'external_message_id', 'message_text', 'send_at'], 'required'],
            [['external_client_id', 'external_message_id'], 'string', 'length' => 32],
            ['message_text', 'string', 'max' => 4096],
            [['dialog_id', 'send_at'], 'integer'],
            [
                ['dialog_id', 'external_client_id', 'external_message_id'],
                'unique',
                'targetAttribute' => [
                    'external_client_id',
                    'external_message_id',
                ],
            ],
            [
                'dialog_id',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Dialog::class,
                'targetAttribute' => ['dialog_id' => 'id']
            ],
            [
                'external_client_id',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Client::class,
                'targetAttribute' => [
                    'external_client_id' => 'external_client_id'
                ]
            ],
        ];
    }

    /**
     * Создаёт новое Сообщение
     *
     * @param array $params
     *
     * @return Message
     */
    public static function create(array $params): Message
    {
        $message = new static();

        $message->external_client_id  = $params['external_client_id'];
        $message->dialog_id           = $params['dialog_id'];
        $message->external_message_id = $params['external_message_id'];
        $message->message_text        = $params['message_text'];
        $message->send_at             = $params['send_at'];

        return $message;
    }

    /**
     * Связь с Диалогом для этого Сообщения
     *
     * @return ActiveQuery
     */
    public function getDialog(): ActiveQuery
    {
        return $this->hasOne(Dialog::class, ['id' => 'dialog_id']);
    }

    /**
     * Связь с Клиентом для этого Сообщения
     *
     * @return ActiveQuery
     */
    public function getClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['external_client_id' => 'external_client_id']);
    }
}
