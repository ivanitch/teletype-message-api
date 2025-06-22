<?php

declare(strict_types=1);

namespace api\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Сообщение
 *
 * @property integer $id ID записи
 * @property integer $dialog_id ID диалога
 * @property string $external_client_id Уникальный идентификатор клиента
 * @property string $external_message_Id Уникальный идентификатор сообщения
 * @property string $message_text Текст сообщения
 * @property integer $send_at Дата-время отправки в формате `unixtime`
 * @property integer $created_at Дата-время создания сообщения
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
            // Составной индекс: external_client_id + external_message_id
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
     * Проверяет, существует ли сообщение с таким `external_client_id` и `external_message_id`.
     *
     * @param string $clientID
     * @param string $messageID
     *
     * @return bool
     */
    public static function isDuplicate(string $clientID, string $messageID): bool
    {
        return self::find()
            ->where([
                'external_client_id'  => $clientID,
                'external_message_id' => $messageID,
            ])
            ->exists();
    }

    /**
     * Связь с диалогом для этого сообщения
     *
     * @return ActiveQuery
     */
    public function getDialog(): ActiveQuery
    {
        return $this->hasOne(Dialog::class, ['id' => 'dialog_id']);
    }

    /**
     * Связь с клиентом для этого сообщения
     *
     * @return ActiveQuery
     */
    public function getClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['external_client_id' => 'external_client_id']);
    }
}
