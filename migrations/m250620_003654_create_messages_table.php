<?php

use yii\db\Migration;

class m250620_003654_create_messages_table extends Migration
{
    private const string MESSAGES_TABLE = '{{%messages}}';

    public function safeUp(): void
    {
        $this->createTable(self::MESSAGES_TABLE, [
            'id' => $this->primaryKey(),

            'dialog_id' => $this->integer()
                ->notNull()
                ->comment('Идентификатор диалога'),

            'external_client_id' => $this->char(32)
                ->notNull()
                ->comment('Идентификатор клиента'),

            'external_message_id' => $this->char(32)
                ->notNull()
                ->comment('Уникальный идентификатор сообщения'),

            'message_text' => $this->text()
                ->notNull()
                ->comment('Сообшение'),

            'send_at' => $this->integer()
                ->notNull()
                ->comment('Дата и время отправки сообщения'),
        ]);

        $this->createIndex(
            'unique-client-dialog-message',
            self::MESSAGES_TABLE,
            ['dialog_id', 'external_client_id', 'external_message_id'],
            true
        );

        $this->addForeignKey(
            'fk-messages-dialog_id',
            self::MESSAGES_TABLE,
            'dialog_id',
            '{{%dialogs}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addCommentOnTable(self::MESSAGES_TABLE, 'Сообщения');
    }

    public function safeDown(): void
    {
        $this->dropTable(self::MESSAGES_TABLE);
    }
}
