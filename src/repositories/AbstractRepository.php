<?php

declare(strict_types=1);

namespace src\repositories;

use RuntimeException;
use src\interfaces\MessageFactoryInterface;
use yii\db\{ActiveRecord, Exception};
use yii\helpers\Json;

abstract class AbstractRepository
{
    protected const string ERROR_VALIDATE = 'Ошибка валидации %1$s: %2$s';
    protected const string ERROR_SAVE = 'Ошибка сохранения %1$s: %2$s';

    /**
     * Сохранение сущностей
     *
     * @param array $params
     *
     * @return MessageFactoryInterface
     */
    abstract public function make(array $params): MessageFactoryInterface;

    /**
     * Проверяет модель на ошибки валидации и, в случае их отсутствия, сохраняет её
     *
     * @param MessageFactoryInterface $model
     * @param string $entity
     *
     * @return void
     *
     * @throws Exception
     */
    protected function save(MessageFactoryInterface $model, string $entity): void
    {
        /* @var ActiveRecord $model */
        if (!$model->validate()) {
            throw new RuntimeException(sprintf(self::ERROR_VALIDATE, $entity, Json::encode($model->errors)));
        }

        if (!$model->save()) {
            throw new RuntimeException(sprintf(self::ERROR_SAVE, $entity, Json::encode($model->errors)));
        }
    }
}
