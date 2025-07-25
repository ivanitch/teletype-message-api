<?php

declare(strict_types=1);

namespace src\models;

use yii\db\{ActiveQuery, ActiveRecord, Exception};

/**
 * Диалог
 *
 * @property integer $id         ID записи в таблице
 * @property integer $client_id  ID клиента внутри системы
 * @property integer $created_at Дата и время создания диалога
 * @property integer $updated_at Дата и время обновления диалога
 *
 * @property-read Client $client   Связь с клиентом
 * @property-read Message $message Связь с сообщением клиента
 */
class Dialog extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%dialogs}}';
    }

    public function rules(): array
    {
        return [
            ['client_id', 'required'],
            ['client_id', 'integer'],
            ['client_id', 'unique'],
            [
                'client_id',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Client::class,
                'targetAttribute' => ['client_id' => 'id']
            ],
        ];
    }

    /**
     * Создаёт новый диалог
     *
     * @param array $params
     *
     * @return Dialog
     */
    public static function create(array $params): Dialog
    {
        $dialog = new static();

        $dialog->client_id = $params['client_id'];

        return $dialog;
    }

    /**
     * Триггерим обновление `updated_at` Диалога ТОЛЬКО при новом Сообщении
     *
     * @return void
     *
     * @throws Exception
     */
    public function touchUpdateAt(): void
    {
        $this->updated_at = time();
        $this->save(false, ['updated_at']);
    }

    /**
     * Связь с клиентом в этом диалоге
     *
     * @return ActiveQuery
     */
    public function getClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * Связь с сообщением в этом диалоге
     *
     * @return ActiveQuery
     */
    public function getMessages(): ActiveQuery
    {
        return $this->hasMany(Message::class, ['dialog_id' => 'id']);
    }
}