<?php

use yii\db\Migration;

class m250619_103644_create_clients_table extends Migration
{
    private const string CLIENTS_TABLE = '{{%clients}}';

    public function safeUp(): void
    {
        $this->createTable(self::CLIENTS_TABLE, [
            'id'                 => $this->primaryKey(),
            'external_client_id' => $this->char(32)->notNull()->unique()->comment('Уникальный внешний идентификатор клиента'),
            'client_phone'       => $this->string(12)->notNull()->unique()->comment('Номер телефона клиента'),
            'created_at'         => $this->integer()->notNull()->defaultExpression('EXTRACT(EPOCH FROM NOW())')->comment('Дата создания клиента'),
        ]);

        $this->createIndex(
            'idx-clients-external_client_id',
            self::CLIENTS_TABLE,
            'external_client_id',
            true
        );

        $this->createIndex(
            'idx-clients-phone',
            self::CLIENTS_TABLE,
            'client_phone',
            true
        );

        $this->addCommentOnTable(self::CLIENTS_TABLE, 'Клиенты');
    }

    public function safeDown(): void
    {
        $this->dropTable(self::CLIENTS_TABLE);
    }
}
