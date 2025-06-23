<?php

namespace Triyatna\Digiflazz;

use Illuminate\Support\Facades\Facade;
use Triyatna\Digiflazz\Services\DigiflazzClient;

/**
 * @see \Triyatna\Digiflazz\Services\DigiflazzClient
 */
class Digiflazz extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DigiflazzClient::class;
    }
}
