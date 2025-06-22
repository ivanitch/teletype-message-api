<?php

declare(strict_types=1);

namespace api\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Клиент
 *
 * @property integer $id ID записи
 * @property string $external_client_id Уникальный внешний идентификатор клиента
 * @property string $client_phone Уникальный номер телефона клиента
 *
 * @property-read Dialog $dialog Связь с диалогом клиента
 */
class Client extends ActiveRecord
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
            ['client_phone', 'match', 'pattern' => '/^\+7\d{10}$/'],
            ['client_phone', 'string', 'length' => 12],
            [['external_client_id', 'client_phone'], 'unique'],
        ];
    }

    /**
     * Связь с диалогом для этого клиента
     *
     * @return ActiveQuery
     */
    public function getDialog(): ActiveQuery
    {
        return $this->hasOne(Dialog::class, ['client_id' => 'id']);
    }
}
