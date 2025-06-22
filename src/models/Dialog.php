<?php

declare(strict_types=1);

namespace api\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;

/**
 * Диалог
 *
 * @property integer $id ID записи
 * @property integer $client_id ID клиента внутри системы
 * @property integer $created_at Дата и время создания диалога
 * @property integer $updated_at Дата и время обновления диалога
 *
 * @property-read Client $client Связь с клиентом
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
     * Триггерим обновление `updated_at` через "пустой" UPDATE в диалоге ТОЛЬКО при новом сообщении
     *
     * @return void
     *
     * @throws Exception
     */
    public function touch(): void
    {
        Yii::$app->db->createCommand()
            ->update(static::tableName(), ['updated_at' => new Expression('updated_at')], ['id' => $this->id])
            ->execute();
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