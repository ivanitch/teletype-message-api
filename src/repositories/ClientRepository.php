<?php

declare(strict_types=1);

namespace src\repositories;

use src\models\Client;

class ClientRepository extends AbstractRepository
{
    /**
     * @param array $params
     *
     * @return bool
     */
    public function exists(array $params): bool
    {
        return Client::find()->where($params)->exists();
    }

    /**
     * Поиск Клиента по (`external_client_id` + `client_phone`)
     *
     * @param array $params
     *
     * @return Client|null
     */
    public function find(array $params): Client|null
    {
        return Client::findOne($params);
    }

    /**
     * Возвращает существующего Клиента или сохраняет нового
     *
     * @param array $params
     *
     * @return Client
     */
    public function make(array $params): Client
    {
        $client = $this->find($params);

        if (!$client) {
            $client = Client::create($params);
            $this->save($client, 'клиента');
        }

        return $client;
    }
}
