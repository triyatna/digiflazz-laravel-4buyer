<?php

namespace Triyatna\Digiflazz\Http;

use Illuminate\Support\Facades\Http;
use Triyatna\Digiflazz\Exceptions\DigiflazzException;

class Client
{
    public function __construct(protected string $baseUrl) {}

    public function post(string $endpoint, array $payload = []): ResponseHandler
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->acceptJson()
            ->post($this->baseUrl . $endpoint, $payload);

        // if ($response->failed()) {
        //     throw new DigiflazzException("HTTP Error: " . $response->status());
        // }

        return new ResponseHandler($response);
    }
}
