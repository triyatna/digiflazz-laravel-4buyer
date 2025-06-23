<?php

namespace Triyatna\Digiflazz\Services;

use Triyatna\Digiflazz\Http\Client;
use Triyatna\Digiflazz\Traits\ManagesAccount;
use Triyatna\Digiflazz\Traits\ManagesProducts;
use Triyatna\Digiflazz\Traits\ManagesTransactions;
use Triyatna\Digiflazz\Traits\ManagesUtilities;

class DigiflazzClient
{
    use ManagesAccount, ManagesProducts, ManagesTransactions, ManagesUtilities;

    protected Client $client;
    protected string $username;
    protected string $apiKey;

    public function __construct(string $username, string $apiKey)
    {
        $this->username = $username;
        $this->apiKey = $apiKey;
        $baseUrl = 'https://api.digiflazz.com/v1';
        $this->client = new Client($baseUrl);
    }
}
