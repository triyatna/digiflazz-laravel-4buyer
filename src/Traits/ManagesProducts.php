<?php

namespace Triyatna\Digiflazz\Traits;

use Triyatna\Digiflazz\Helpers\Signature;
use Triyatna\Digiflazz\Http\ResponseHandler;

trait ManagesProducts
{
    /**
     * Mendapatkan daftar harga.
     *
     * @param string $cmd 'prepaid' atau 'pasca'.
     * @param array $filters Filter opsional. Contoh: ['code' => 'XLD10', 'brand' => 'XL']
     * Key yang didukung: 'code', 'category', 'brand', 'type'.
     * @return ResponseHandler
     */
    public function getPriceList(string $cmd = 'prepaid', array $filters = []): ResponseHandler
    {
        $payload = ['cmd' => $cmd, 'username' => $this->username, 'sign' => Signature::generate($this->username, $this->apiKey, 'pricelist')];
        foreach ($filters as $key => $value) {
            if (in_array($key, ['code', 'category', 'brand', 'type']) && !empty($value)) {
                $payload[$key] = $value;
            }
        }
        return $this->client->post('/price-list', $payload);
    }
}
