<?php

use yii\db\Migration;

class m250619_150958_create_dialogs_table extends Migration
{
    private const string DIALOGS_TABLE = '{{%dialogs}}';

    public function safeUp(): void
    {
        $this->createTable(self::DIALOGS_TABLE, [
            'id' => $this->primaryKey(),

            'client_id' => $this->integer()
                ->notNull()
                ->comment('Идентификатор клиента внутри системы'),

            'created_at' => $this->integer()
                ->notNull()
                ->defaultExpression('EXTRACT(EPOCH FROM NOW())')
                ->comment('Дата создания диалога'),

            'updated_at' => $this->integer()
                ->notNull()
                ->defaultExpression('EXTRACT(EPOCH FROM NOW())')
                ->comment('Дата обновления диалога')
        ]);

        // Обновление диалога
        $this->createFunctionAndTriggerForUpdatedAtColumn();

        $this->createIndex(
            'uniq-dialog-client',
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
        // Удаляем триггер
        $this->execute("DROP TRIGGER IF EXISTS set_updated_at ON " . self::DIALOGS_TABLE);

        // Удаляем функцию
        $this->execute("DROP FUNCTION IF EXISTS update_updated_at_column");

        $this->dropTable(self::DIALOGS_TABLE);
    }

    /**
     * Создает функцию и триггер обновления `updated_at`
     *
     * @return void
     */
    private function createFunctionAndTriggerForUpdatedAtColumn(): void
    {
        $this->execute("
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = EXTRACT(EPOCH FROM now());
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        $this->execute("
            CREATE TRIGGER trg_update_dialog_updated_at
            BEFORE UPDATE ON dialogs
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
        ");
    }
}
