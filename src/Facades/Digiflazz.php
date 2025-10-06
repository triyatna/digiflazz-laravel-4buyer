<?php

namespace Triyatna\DigiflazzBuyer\Facades;

use Illuminate\Support\Facades\Facade;
use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClientInterface;

/**
 * @method static array checkBalance()
 * @method static array priceList(array|string $payload = [], array $filters = [])
 * @method static array deposit(array|int $payload, ?string $bank = null, ?string $ownerName = null, array $extra = [])
 * @method static array topupPrepaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = [])
 * @method static array inquiryPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = [])
 * @method static array payPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = [])
 * @method static array statusPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = [])
 * @method static array inquiryPln(array|string $customerNoOrPayload)
 * @method static \Triyatna\DigiflazzBuyer\DTO\BalanceDto checkBalanceDto()
 * @method static \Triyatna\DigiflazzBuyer\DTO\TransactionDto topupPrepaidDto(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = [])
 */
class Digiflazz extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DigiflazzClientInterface::class;
    }
}
