<?php

namespace Triyatna\DigiflazzBuyer\Support;

final class Signature
{
    public static function sign(string $username, string $apiKey, ?string $ref = null, ?string $customerNo = null): string
    {
        if ($ref !== null) {
            return md5($username.$apiKey.$ref);
        }
        if ($customerNo !== null) {
            return md5($username.$apiKey.$customerNo);
        }
        return md5($username.$apiKey.'depo');
    }
}
