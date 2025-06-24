<?php

namespace Triyatna\Digiflazz\Http;

use Illuminate\Support\Facades\Http;
use Triyatna\Digiflazz\Exceptions\DigiflazzException;

class Client
{
    public function __construct(protected string $baseUrl) {}

    /**
     * Mengirim request POST ke endpoint Digiflazz.
     *
     * @param string $endpoint
     * @param array $payload
     * @return ResponseHandler
     * @throws DigiflazzException
     */
    public function post(string $endpoint, array $payload = []): ResponseHandler
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->acceptJson()
            ->post($this->baseUrl . $endpoint, $payload);

        if ($response->serverError()) {
            throw new DigiflazzException(
                "Digiflazz Server Error: " . $response->status(),
                $response->status()
            );
        }
        // if ($response->failed()) {
        //     throw new DigiflazzException("HTTP Error: " . $response->status());
        // }

        return new ResponseHandler($response);
    }
}
