<?php

namespace Triyatna\Digiflazz\Helpers;

class Signature
{
    public static function generate(string $username, string $apiKey, string $key): string
    {
        return md5($username . $apiKey . $key);
    }
}
