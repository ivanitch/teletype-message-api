<?php

declare(strict_types=1);

namespace src\controllers\v1;

use src\controllers\BaseRestController;
use src\forms\MessageForm;
use src\models\Message;
use src\services\MessageService;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;


/**
 * Контроллер для добавления собщения
 * 👉 Клиент ((`external_id` - external message device) + `phone`)
 *          -> Один Диалог для одного Клиента
 *              -> Уникальное Сообщение в диалоге
 */
class MessageController extends BaseRestController
{
    /**
     * @param $id
     * @param $module
     * @param MessageService $service
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        private readonly MessageService $service,
        array $config = []
    )
    {
        parent::__construct($id, $module, $config);
    }


    /**
     * Метод для отправки сообщения и получение информативного результата
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

        try {
            return $this->make($form);
        } catch (Throwable $e) {
            Yii::error("Ошибка при добавлении сообщения: {$e->getMessage()}");
            throw new BadRequestHttpException('Возникла внутренняя ошибка.');
        }
    }

    /**
     * @param MessageForm $form
     *
     * @return array
     *
     * @throws Throwable
     * @throws Exception
     */
    private function make(MessageForm $form): array
    {
        if (!$form->validate()) {
            Yii::warning('Ошибка валидации формы: ' . Json::encode($form->errors));
            return $this->showResult($form);
        }

        return $this->showResult($form, $this->service->process($form));
    }

    /**
     * Информативный ответ-результат
     *
     * @param MessageForm $form
     * @param null|Message $message
     *
     * @return array
     */
    private function showResult(MessageForm $form, null|Message $message = null): array
    {
        return match (true) {
            $message === null => [
                'success' => false,
                'data'    => null,
                'errors'  => $form->errors,
            ],
            default => [
                'success' => true,
                'data'    => $message,
                'errors'  => [],
            ],
        };
    }
}
