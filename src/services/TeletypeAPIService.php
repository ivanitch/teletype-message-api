<?php

declare(strict_types=1);

namespace src\services;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\httpclient\Response;
use yii\web\HttpException;

/**
 * Тест для Себя ☝️
 * ❗ Токен удалён в целях безопасности (сервис не работает) ❗
 *
 * @see: API DOC -> https://api.teletype.app/public/api/v1/
 */
class TeletypeAPIService extends Component
{
    private const string BASE_URL = 'https://api.teletype.app/public/api/v1';
    private const string TOKEN = '...';

    /**
     * @param Client $httpClient
     * @param $config
     */
    public function __construct(readonly private Client $httpClient, $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Получение данных с удалённого сервера
     *
     * @param string $URL
     *
     * @return array
     *
     * @throws Exception
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function getData(string $URL): array
    {
        $response = $this->getResponse($URL);

        if (!$response->isOk) {
            throw new HttpException($response->statusCode, 'Teletype API error: ' . $response->content);
        }

        return $response->data;
    }

    /**
     * @param string $URL
     *
     * @return Response
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function getResponse(string $URL): Response
    {
        return $this->httpClient->createRequest()
            ->setMethod('GET')
            ->setUrl(static::BASE_URL . DIRECTORY_SEPARATOR . $URL)
            ->setHeaders([
                'X-Auth-Token' => static::TOKEN,
                'Accept'       => 'application/json',

            ])
            ->send();
    }
}
