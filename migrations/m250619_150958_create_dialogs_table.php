<?php

use yii\db\Migration;

class m250619_150958_create_dialogs_table extends Migration
{
    private const string DIALOGS_TABLE = '{{%dialogs}}';

    public function safeUp(): void
    {
        $this->createTable(self::DIALOGS_TABLE, [
            'id'         => $this->primaryKey(),
            'client_id'  => $this->integer()->notNull()->unique()->comment('Уникальный идентификатор клиента'),
            'created_at' => $this->integer()->notNull()->defaultExpression('EXTRACT(EPOCH FROM NOW())')->comment('Дата создания диалога'),
        ]);

        $this->createIndex(
            'idx-dialogs-client_id',
            self::DIALOGS_TABLE,
            'client_id',
            true
        );

        $this->addForeignKey(
            'fk-dialogs-client_id',
            self::DIALOGS_TABLE,
            'client_id',
            '{{%clients}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addCommentOnTable(self::DIALOGS_TABLE, 'Диалоги');
    }

    public function safeDown(): void
    {
        $this->dropTable(self::DIALOGS_TABLE);
    }
}
