<?php

use Illuminate\Support\Facades\Http;
use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClient;
use Orchestra\Testbench\TestCase;

class DtoTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Triyatna\DigiflazzBuyer\DigiflazzServiceProvider::class];
    }

    public function test_returns_transaction_dto()
    {
        $client = new DigiflazzClient('https://api.example.com', 'u', 'k');
        Http::fake([
            'https://api.example.com/transaction' => Http::response(['data' => ['rc' => '00', 'status' => 'Sukses', 'ref_id' => 'R1']], 200),
        ]);
        $dto = $client->topupPrepaidDto(['ref_id' => 'R1', 'buyer_sku_code' => 'S', 'customer_no' => 'C']);
        $this->assertSame('R1', $dto->refId);
        $this->assertSame('Sukses', $dto->status);
    }
}
