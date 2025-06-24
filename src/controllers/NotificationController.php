<?php

declare(strict_types=1);

namespace src\controllers;

use src\services\TeletypeAPIService;
use Throwable;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Получение данных по API с api.teletype.app
 *
 * @see TeletypeAPIService
 * @see "Teletype Public API Doc" https://teletype.app/help/api/
 * @see "Публичное API Settings" https://neo-teletype.teletype.app/settings/public-api
 */
class NotificationController extends BaseRestController
{
    private const string CLIENTS_URL = 'clients';
    private const string DIALOGS_URL = 'dialogs';
    private const string CHANNELS_URL = 'channels';
    private const string MESSAGES_URL = 'messages';

    public function __construct(
                                            $id,
                                            $module,
        private readonly TeletypeAPIService $teletypeAPIService,
        array                               $config = []
    )
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * Клиенты
     *
     * GET /remote/clients
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    public function actionClients(): array
    {
        try {
            return $this->teletypeAPIService->getData(URL: static::CLIENTS_URL);
        } catch (Throwable $e) {
            Yii::error("Get clients processing failed: " . $e->getMessage());
            throw new BadRequestHttpException('Internal error');
        }
    }

    /**
     * Каналы
     *
     * /remote/channels
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    public function actionChannels(): array
    {
        try {
            return $this->teletypeAPIService->getData(URL:static::CHANNELS_URL);
        } catch (Throwable $e) {
            Yii::error("Get channels processing failed: " . $e->getMessage());
            throw new BadRequestHttpException('Internal error');
        }
    }

    /**
     * Диалоги
     *
     * /remote/dialogs
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    public function actionDialogs(): array
    {
        try {
            return $this->teletypeAPIService->getData(URL: static::DIALOGS_URL);
        } catch (Throwable $e) {
            Yii::error("Get dialogs processing failed: " . $e->getMessage());
            throw new BadRequestHttpException('Internal error');
        }
    }

    /**
     * Сообщения
     *
     * /remote/messages
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    public function actionMessages(): array
    {
        try {
            return $this->teletypeAPIService->getData(URL: static::MESSAGES_URL);
        } catch (Throwable $e) {
            Yii::error("Messages processing failed: " . $e->getMessage());
            throw new BadRequestHttpException('Internal error');
        }
    }
}
