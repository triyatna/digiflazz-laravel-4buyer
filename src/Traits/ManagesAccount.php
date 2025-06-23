<?php

namespace Triyatna\Digiflazz\Traits;

use InvalidArgumentException;
use Triyatna\Digiflazz\Helpers\Signature;
use Triyatna\Digiflazz\Http\ResponseHandler;


trait ManagesAccount
{
    public function checkBalance(): ResponseHandler
    {
        $payload = ['cmd' => 'deposit', 'username' => $this->username, 'sign' => Signature::generate($this->username, $this->apiKey, 'depo')];
        return $this->client->post('/cek-saldo', $payload);
    }

    public function requestDeposit(int $amount, string $bank, string $ownerName): ResponseHandler
    {
        // validasi bank yang tersedia dengan sensitif case
        $sanitizedBank = strtoupper($bank);
        $availableBanks = ['BCA', 'BNI', 'BRI', 'MANDIRI'];
        if (!in_array($sanitizedBank, $availableBanks, true)) {
            throw new InvalidArgumentException(
                'Bank tidak tersedia. Pilih dari: ' . implode(', ', $availableBanks)
            );
        }

        $payload = ['username' => $this->username, 'amount' => $amount, 'Bank' => $sanitizedBank, 'owner_name' => $ownerName, 'sign' => Signature::generate($this->username, $this->apiKey, 'deposit')];
        return $this->client->post('/deposit', $payload);
    }
}
