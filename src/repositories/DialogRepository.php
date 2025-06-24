<?php

declare(strict_types=1);

namespace src\repositories;

use src\interfaces\MessageFactoryInterface;
use src\models\Dialog;
use yii\db\Exception;

class DialogRepository extends AbstractRepository
{
    /**
     * Поиск Диалога Клиента
     *
     * @param array $params
     *
     * @return Dialog|null
     */
    public function find(array $params): MessageFactoryInterface|null
    {
        return Dialog::findOne($params);
    }

    /**
     * Возвращает существующий Диалог или Сохраняет новый Диалог
     *
     * @param array $params
     *
     * @return Dialog
     *
     * @throws Exception
     */
    public function make(array $params): MessageFactoryInterface
    {
        $dialog = $this->find($params) ?? Dialog::create($params);

        if ($dialog->isNewRecord) {
            $this->save($dialog, 'диалога');
        }

        return $dialog;
    }
}
