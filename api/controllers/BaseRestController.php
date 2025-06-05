<?php

declare(strict_types=1);

namespace api\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\rest\ActiveController;
use yii\web\Response;

class BaseRestController extends ActiveController
{
    public $serializer = [
        'class'              => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    protected mixed $args;

    public function __construct(
        $id,
        $module,
        $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->args     = Yii::$app->request->queryParams;
        $this->response = Yii::$app->getResponse();
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class'       => ContentNegotiator::class,
            'formatParam' => '_format',
            'formats'     => [
                'application/json' => Response::FORMAT_JSON
            ],
        ];

        return $behaviors;
    }
}
