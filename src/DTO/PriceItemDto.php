<?php

namespace Triyatna\DigiflazzBuyer\DTO;

class PriceItemDto
{
    public function __construct(
        public readonly string $buyerSkuCode,
        public readonly string $productName,
        public readonly string $brand,
        public readonly string $category,
        public readonly string $type,
        public readonly int $price,
        public readonly bool $buyerProductStatus,
        public readonly ?array $raw = null,
    ) {}

    public static function from(array $item): self
    {
        return new self(
            (string) ($item['buyer_sku_code'] ?? ''),
            (string) ($item['product_name'] ?? ''),
            (string) ($item['brand'] ?? ''),
            (string) ($item['category'] ?? ''),
            (string) ($item['type'] ?? ''),
            (int) ($item['price'] ?? 0),
            (bool) ($item['buyer_product_status'] ?? true),
            $item
        );
    }
}
