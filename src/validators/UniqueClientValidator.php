<?php

declare(strict_types=1);

namespace src\validators;

use src\repositories\ClientRepository;
use yii\validators\Validator;
use src\models\Client;

/**
 * Проверяет на существование клиента с таким `external_client_id` + `client_phone`
 */
class UniqueClientValidator extends Validator
{
    private const string ERROR_MESSAGE = 'Клиент %1$s с номером телефона %2$s уже существует.';

    public function __construct(private readonly ClientRepository $clientRepository, $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @param $model
     * @param $attribute
     *
     * @return void
     */
    public function validateAttribute($model, $attribute): void
    {
        /* @var Client $model */
        $clientId = $model->external_client_id;
        $phone    = $model->client_phone;

        $params = [
            'external_client_id' => $clientId,
            'client_phone'       => $phone,
        ];

        if ($this->clientRepository->exists($params)) {
            $this->addError($model, $attribute, sprintf(static::ERROR_MESSAGE, $clientId, $phone));
        }
    }
}
