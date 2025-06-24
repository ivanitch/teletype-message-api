<?php

declare(strict_types=1);

namespace src\repositories;

use src\interfaces\MessageFactoryInterface;
use src\models\Client;
use yii\db\Exception;

class ClientRepository extends AbstractRepository
{
    /**
     * Поиск Клиента по (`external_client_id` + `client_phone`)
     *
     * @param array $params
     *
     * @return Client|null
     */
    public function find(array $params): MessageFactoryInterface|null
    {
        return Client::findOne($params);
    }

    /**
     * Возвращает существующего Клиента или Сохраняет нового Клиента
     *
     * @param array $params
     *
     * @return Client
     *
     * @throws Exception
     */
    public function make(array $params): MessageFactoryInterface
    {
        $client = $this->find($params) ?? Client::create($params);

        if ($client->isNewRecord) {
            $this->save($client, 'клиента');
        }

        return $client;
    }
}
