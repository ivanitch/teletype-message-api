<?php

declare(strict_types=1);

namespace src\models;

use src\interfaces\MessageFactoryInterface;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Клиент
 *
 * @property integer $id                ID записи в таблице
 * @property string $external_client_id Уникальный внешний идентификатор Клиента
 * @property string $client_phone       Уникальный Номер телефона Клиента
 *
 * @property-read Dialog $dialog        Связь с Диалогом Клиента
 */
class Client extends ActiveRecord implements MessageFactoryInterface
{
    public static function tableName(): string
    {
        return '{{%clients}}';
    }

    public function rules(): array
    {
        return [
            [['external_client_id', 'client_phone'], 'required'],
            ['external_client_id', 'string', 'length' => 32],
            [['external_client_id', 'client_phone'], 'unique'],
        ];
    }

    /**
     * Создаёт нового Клиента
     *
     * @param array $params
     *
     * @return MessageFactoryInterface
     */
    public static function create(array $params): MessageFactoryInterface
    {
        $client                     = new static();

        $client->external_client_id = $params['external_client_id'];
        $client->client_phone       = $params['client_phone'];

        return $client;
    }

    /**
     * Связь с Диалогом для этого Клиента
     *
     * @return ActiveQuery
     */
    public function getDialog(): ActiveQuery
    {
        return $this->hasOne(Dialog::class, ['client_id' => 'id']);
    }
}
