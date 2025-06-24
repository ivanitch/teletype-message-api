<?php

declare(strict_types=1);

namespace src\services;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\web\HttpException;

/**
 * Тест для Себя ☝️
 *
 * @see: API DOC -> https://api.teletype.app/public/api/v1/
 */
class TeletypeAPIService extends Component
{
    private const string BASE_URL = 'https://api.teletype.app/public/api/v1';
    private const string TOKEN = '1unUEMyNY3NTM7CRA9TeIBUSt5ZDJtiNflbC1yisBigxjSyXutiilgnl3gh_M8KO';

    /**
     * Получение данных с удалённого сервера

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
        $response = $this->httpClient->createRequest()
            ->setMethod('GET')
            ->setUrl(static::BASE_URL . DIRECTORY_SEPARATOR . $URL)
            ->setHeaders([
                'X-Auth-Token' => static::TOKEN,
                'Accept'       => 'application/json',

            ])
            ->send();

        if (!$response->isOk) {
            throw new HttpException($response->statusCode, 'Teletype API error: ' . $response->content);
        }

        return $response->data;
    }

    public function __construct(readonly private Client $httpClient, $config = [])
    {
        parent::__construct($config);
    }
}
