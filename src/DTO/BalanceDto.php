<?php

namespace Triyatna\DigiflazzBuyer\DTO;

class BalanceDto
{
    public function __construct(
        public readonly int $deposit,
        public readonly ?int $hold = null,
        public readonly ?array $raw = null,
    ) {}

    public static function from(array $json): self
    {
        $deposit = (int) (data_get($json, 'data.deposit') ?? data_get($json, 'deposit', 0));
        $hold = data_get($json, 'data.hold');
        return new self($deposit, $hold, $json);
    }
}
