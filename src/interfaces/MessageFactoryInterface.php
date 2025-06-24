<?php

declare(strict_types=1);

namespace src\interfaces;

interface MessageFactoryInterface
{
    public static function create(array $params): MessageFactoryInterface;
}
