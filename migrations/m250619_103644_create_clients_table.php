<?php

use yii\db\Migration;

class m250619_103644_create_clients_table extends Migration
{
    private const string CLIENTS_TABLE = '{{%clients}}';

    public function safeUp(): void
    {
        $this->createTable(self::CLIENTS_TABLE, [
            'id' => $this->primaryKey(),

            'external_client_id' => $this->char(32)
                ->notNull()
                ->comment('Уникальный внешний идентификатор клиента'),

            'client_phone' => $this->string(12)
                ->notNull()
                ->comment('Номер телефона клиента'),
        ]);

        $this->createIndex(
            'unique-client',
            self::CLIENTS_TABLE,
            ['external_client_id', 'client_phone'],
            true
        );

        $this->addCommentOnTable(self::CLIENTS_TABLE, 'Клиенты');
    }

    public function safeDown(): void
    {
        $this->dropTable(self::CLIENTS_TABLE);
    }
}
