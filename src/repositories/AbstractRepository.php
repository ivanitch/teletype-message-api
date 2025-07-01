<?php

declare(strict_types=1);

namespace src\repositories;

use RuntimeException;
use yii\db\ActiveRecord;
use yii\helpers\Json;

abstract class AbstractRepository
{
    protected const string ERROR_VALIDATE = 'Ошибка валидации %1$s: %2$s';
    protected const string ERROR_SAVE = 'Ошибка сохранения %1$s: %2$s';

    /**
     * Проверяет на существование сущности в БД
     *
     * @param array $params
     *
     * @return bool
     */
    abstract public function exists(array $params): bool;

    /**
     * Поиск сущности в БД
     *
     * @param array $params
     *
     * @return ActiveRecord|null
     */
    abstract public function find(array $params): ActiveRecord|null;

    /**
     * Сохранение сущностей
     *
     * @param array $params
     *
     * @return ActiveRecord
     */
    abstract public function make(array $params): ActiveRecord;

    /**
     * Проверяет модель на ошибки валидации и сохраняет её в БД
     */
    protected function save(ActiveRecord $model, string $entity): void
    {
        if (!$model->validate()) {
            throw new RuntimeException(sprintf(self::ERROR_VALIDATE, $entity, Json::encode($model->errors)));
        }

        if (!$model->save()) {
            throw new RuntimeException(sprintf(self::ERROR_SAVE, $entity, Json::encode($model->errors)));
        }
    }
}
