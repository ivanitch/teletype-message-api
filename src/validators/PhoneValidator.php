<?php

namespace src\validators;

use yii\validators\Validator;

class PhoneValidator extends Validator
{
    public string $pattern = '/^\+7\d{10}$/';

    public $message = 'Номер телефона должен быть в формате `+7XXXXXXXXXX` и состоять из 12-ти символов';

    public function validateAttribute($model, $attribute): void
    {
        $value = preg_replace('/\s+/', '', trim($model->$attribute));

        $model->$attribute = $value;

        if (!preg_match($this->pattern, $value)) {
            $this->addError($model, $attribute, $this->message);
        }
    }
}
