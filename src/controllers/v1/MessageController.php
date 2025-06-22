<?php

declare(strict_types=1);

namespace api\controllers\v1;

use api\controllers\BaseRestController;
use api\forms\MessageForm;
use api\models\Message;
use api\services\MessageService;
use Throwable;
use Yii;
use yii\filters\ContentNegotiator;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 *
 */
class MessageController extends BaseRestController
{
    /**
     * @param $id
     * @param $module
     * @param MessageService $messageService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        private readonly MessageService $messageService,
        array $config = []
    )
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class'   => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    /**
     * Отправка сообщения и получение информативного результата
     *
     * Route: POST /api/v1/messages
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    public function actionCreate(): array
    {
        $form = new MessageForm();
        $form->load(Yii::$app->request->post(), '');

        if (!$form->validate()) {
            Yii::warning("Validation failed: " . json_encode($form->errors));
            return $this->getResult($form);
        }

        try {
            $message = $this->messageService->process($form);
            return $this->getResult($form, $message);
        } catch (Throwable $e) {
            Yii::error("Error processing adding message: " . $e->getMessage());
            throw new BadRequestHttpException('Internal error');
        }
    }

    /**
     * @param MessageForm $form
     * @param null|Message $message
     *
     * @return array
     */
    private function getResult(MessageForm $form, null|Message $message = null): array
    {
        return [
            'success' => $message !== null,
            'data'    => $message,
            'errors'  => $message === null ? $form->errors : [],
        ];
    }
}
