<?php

declare(strict_types=1);

namespace api\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Диалог
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $created_at
 *
 * @property Client $client
 * @property Message $message
 */
class Dialog extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'dialogs';
    }

    public function rules(): array
    {
        return [
            ['client_id', 'required'],
            ['client_id', 'integer'],
            [
                'client_id',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Client::class,
                'targetAttribute' => ['client_id' => 'id']
            ],
        ];
    }

    public function getClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getMessages(): ActiveQuery
    {
        return $this->hasMany(Message::class, ['dialog_id' => 'id']);
    }
}