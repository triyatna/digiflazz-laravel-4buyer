<?php

namespace Triyatna\Digiflazz\Traits;

use Triyatna\Digiflazz\Helpers\Signature;
use Triyatna\Digiflazz\Http\ResponseHandler;

trait ManagesUtilities
{
    /**
     * Melakukan inquiry nama pelanggan PLN Prabayar.
     */
    public function inquiryPln(string $customerNo): ResponseHandler
    {
        $payload = [
            'customer_no' => $customerNo,
            'username' => $this->username,
            'sign' => Signature::generate($this->username, $this->apiKey, $customerNo)
        ];

        return $this->client->post('/inquiry-pln', $payload);
    }
    /**
     * Memicu 'ping' event ke webhook yang terdaftar.
     */

    public function pingWebhook(string $webhookId): ResponseHandler
    {
        return $this->client->post("/report/hooks/{$webhookId}/pings");
    }
}
