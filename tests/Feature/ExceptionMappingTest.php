<?php

use Illuminate\Support\Facades\Http;
use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClient;
use Triyatna\DigiflazzBuyer\Exceptions\{PendingException, TimeoutException, ValidationException, InsufficientBalanceException, DuplicateRefIdException, ProductUnavailableException, CutOffException, RateLimitedException, NotFoundException};
use Orchestra\Testbench\TestCase;

class ExceptionMappingTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Triyatna\DigiflazzBuyer\DigiflazzServiceProvider::class];
    }

    /**
     * @dataProvider rcProvider
     */
    public function test_maps_rc_to_exceptions(string $rc, string $exception)
    {
        $client = new DigiflazzClient('https://api.example.com', 'u', 'k');

        Http::fake([
            'https://api.example.com/transaction' => Http::response(['data' => ['rc' => $rc, 'message' => 'x']], 200),
        ]);

        $this->expectException($exception);
        $client->topupPrepaid(['ref_id' => 'R', 'buyer_sku_code' => 'S', 'customer_no' => 'C']);
    }

    public static function rcProvider(): array
    {
        return [
            ['03', PendingException::class],
            ['01', TimeoutException::class],
            ['40', ValidationException::class],
            ['44', InsufficientBalanceException::class],
            ['49', DuplicateRefIdException::class],
            ['53', ProductUnavailableException::class],
            ['58', CutOffException::class],
            ['83', RateLimitedException::class],
            ['50', NotFoundException::class],
        ];
    }
}
