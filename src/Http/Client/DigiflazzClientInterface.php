<?php

namespace Triyatna\DigiflazzBuyer\Http\Client;

use Triyatna\DigiflazzBuyer\DTO\{BalanceDto, TransactionDto};

interface DigiflazzClientInterface
{
    public function checkBalance(): array;

    public function priceList(array|string $payload = [], array $filters = []): array;

    public function deposit(array|int $payload, ?string $bank = null, ?string $ownerName = null, array $extra = []): array;

    public function topupPrepaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array;

    public function inquiryPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array;

    public function payPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array;

    public function statusPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array;

    public function inquiryPln(array|string $customerNoOrPayload): array;

    public function checkBalanceDto(): BalanceDto;
    public function topupPrepaidDto(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): TransactionDto;
}
