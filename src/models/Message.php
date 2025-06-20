<?php

declare(strict_types=1);

namespace api\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $dialog_id
 * @property string $external_message_id
 * @property string $message_text
 * @property integer $send_at
 * @property integer $created_at
 *
 * @property Dialog $dialog
 */
class Message extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'messages';
    }

    public function rules(): array
    {
        return [
            [['dialog_id', 'external_message_id', 'text', 'send_at'], 'required'],
            ['external_message_id', 'string', 'length' => 32],
            ['text', 'string', 'max' => 4096],
            ['send_at', 'integer'],
        ];
    }

    public function getDialog(): ActiveQuery
    {
        return $this->hasOne(Dialog::class, ['id' => 'dialog_id']);
    }
}
