<?php

declare(strict_types=1);

namespace api\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Клиент
 *
 * @property integer $id
 * @property string $external_client_id
 * @property string $client_phone
 * @property integer $created_at
 *
 * @property Dialog $dialog
 */
class Client extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'clients';
    }

    public function rules(): array
    {
        return [
            [['external_client_id', 'phone'], 'required'],
            ['external_client_id', 'string', 'length' => 32],
            ['phone', 'match', 'pattern' => '/^\+7\d{10}$/'],
            ['phone', 'string', 'length' => 12],
        ];
    }

    public function getDialog(): ActiveQuery
    {
        return $this->hasOne(Dialog::class, ['client_id' => 'id']);
    }
}
