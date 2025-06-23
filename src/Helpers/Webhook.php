<?php

namespace Triyatna\Digiflazz\Helpers;

class Webhook
{
    public static function validate(string $signatureHeader, string $payload, string $secret): bool
    {
        if (empty($signatureHeader) || !str_starts_with($signatureHeader, 'sha1=')) {
            return false;
        }
        $expected = hash_hmac('sha1', $payload, $secret);
        $actual = substr($signatureHeader, 5);
        return hash_equals($expected, $actual);
    }
}
