<?php

namespace Triyatna\DigiflazzBuyer\Support;

use Triyatna\DigiflazzBuyer\DTO\{BalanceDto, PriceItemDto, TransactionDto};

final class ResponseMapper
{
    public static function balance(array $json): BalanceDto
    {
        return BalanceDto::from($json);
    }

    /** @return array<int, PriceItemDto> */
    public static function priceList(array $json): array
    {
        $items = $json['data'] ?? $json;
        if (!is_array($items)) return [];
        $items = array_values($items);
        return array_map(fn($i) => PriceItemDto::from($i), $items);
    }

    public static function transaction(array $json): TransactionDto
    {
        return TransactionDto::from($json);
    }
}
