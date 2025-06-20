<?php

use yii\db\Migration;

class m250620_003654_create_messages_table extends Migration
{
    private const string MESSAGES_TABLE = '{{%messages}}';

    public function safeUp(): void
    {
        $this->createTable(self::MESSAGES_TABLE, [
            'id'                  => $this->primaryKey(),
            'dialog_id'           => $this->integer()->unique()->notNull()->comment('Уникальный идентификатор диалога'),
            'external_message_id' => $this->char(32)->notNull()->unique()->comment('Уникальный идентификатор сообщения'),
            'message_text'        => $this->text()->notNull()->comment('Сообшение'),
            'send_at'             => $this->integer()->notNull()->comment('Дата и время отправки сообщения'),
            'created_at'          => $this->integer()->notNull()->defaultExpression('EXTRACT(EPOCH FROM NOW())')->comment('Дата создания сообщения'),
        ]);

        $this->createIndex(
            'idx-messages-external_message_id',
            self::MESSAGES_TABLE,
            'external_message_id',
            true
        );

        $this->createIndex(
            'idx-messages-dialog_id',
            self::MESSAGES_TABLE,
            'dialog_id',
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
