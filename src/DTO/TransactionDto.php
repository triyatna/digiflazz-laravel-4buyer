<?php

namespace Triyatna\DigiflazzBuyer\DTO;

class TransactionDto
{
    public function __construct(
        public readonly string $refId,
        public readonly string $status,
        public readonly string $rc,
        public readonly string $message,
        public readonly ?string $sn = null,
        public readonly ?string $buyerSkuCode = null,
        public readonly ?string $customerNo = null,
        public readonly ?int $price = null,
        public readonly ?array $raw = null,
    ) {}

    public static function from(array $json): self
    {
        $data = $json['data'] ?? $json;
        return new self(
            (string) ($data['ref_id'] ?? ''),
            (string) ($data['status'] ?? ($json['status'] ?? '')),
            (string) ($data['rc'] ?? ($json['rc'] ?? '')),
            (string) ($data['message'] ?? ($json['message'] ?? '')),
            $data['sn'] ?? null,
            $data['buyer_sku_code'] ?? null,
            $data['customer_no'] ?? null,
            isset($data['price']) ? (int) $data['price'] : null,
            $json
        );
    }
}
