
# digiflazz-laravel-4buyer (v2)

A modern, safe, and feature‑rich **Digiflazz Buyer API** client for **Laravel 8 → 12**. Built for production: fast defaults, clear error semantics, DTOs, webhook verification, CI + tests.

---

## Table of Contents
- [Introduction](#introduction)
- [Features](#features)
- [Why This Library](#why-this-library)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Environment Keys](#environment-keys)
- [Security Considerations](#security-considerations)
- [Response Codes → Exceptions](#response-codes--exceptions)
- [Usage](#usage)
  - [Check Balance](#check-balance)
  - [Price List (Prepaid/Pasca)](#price-list-prepaidpasca)
  - [Deposit](#deposit)
  - [Prepaid Topup](#prepaid-topup)
  - [Postpaid Inquiry](#postpaid-inquiry)
  - [Postpaid Payment](#postpaid-payment)
  - [Postpaid Status](#postpaid-status)
  - [PLN Inquiry](#pln-inquiry)
- [Webhook](#webhook)
- [DTOs](#dtos)
- [Caching & Performance](#caching--performance)
- [Testing & CI](#testing--ci)
- [Troubleshooting](#troubleshooting)
- [Versioning & Support Matrix](#versioning--support-matrix)
- [Sources](#sources)
- [License](#license)

---

## Introduction
`triyatna/digiflazz-laravel-4buyer` is a Laravel package that implements the **Digiflazz Buyer API** with safe defaults and developer‑friendly ergonomics. It covers balance, price list, deposit, prepaid, postpaid (inquiry/pay/status), PLN inquiry, and webhook verification.

## Features
- HTTP client with **timeouts** and **retries** via Laravel `Http`.
- **Signature** generation aligned with Digiflazz docs.
- **DTOs** for typed access to common responses.
- **Facade** for concise calls.
- **Webhook** controller with HMAC‑SHA1 validation (`X-Hub-Signature`).
- Comprehensive **RC mapping** → **exceptions** for robust error handling.
- **CI + tests** (Pest + Testbench) and PHPStan config.
- Works on **Laravel 8 → 12**.

## Why This Library
- Production‑ready defaults to reduce operational risk.
- Minimal boilerplate: call via Facade or inject the client interface.
- Clear exceptions make it easy to implement compensating actions and user messaging.
- Flexible: use raw arrays or DTOs.

## Requirements
- PHP **^8.0**
- Laravel **8–12**

## Installation
```bash
composer require triyatna/digiflazz-laravel-4buyer
php artisan vendor:publish --tag=digiflazz-config
php artisan digiflazz:install-env
```
The installer ensures these keys exist in `.env` (grouped properly with spacing).

## Configuration
File: `config/digiflazz.php`
```php
return [
    'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
    'username' => env('DIGIFLAZZ_USERNAME', ''),
    'api_key'  => env('DIGIFLAZZ_API_KEY', ''),
    'webhook_secret' => env('DIGIFLAZZ_WEBHOOK_SECRET', ''),
    'timeout' => env('DIGIFLAZZ_HTTP_TIMEOUT', 15),
    'retry' => [
        'times' => env('DIGIFLAZZ_HTTP_RETRY_TIMES', 2),
        'sleep_ms' => env('DIGIFLAZZ_HTTP_RETRY_SLEEP_MS', 200),
    ],
    'ip_whitelist' => ['52.74.250.133'],
];
```

## Environment Keys
```env
DIGIFLAZZ_USERNAME=
DIGIFLAZZ_API_KEY=
DIGIFLAZZ_WEBHOOK_SECRET=
# Optional overrides:
# DIGIFLAZZ_BASE_URL=https://api.digiflazz.com/v1
# DIGIFLAZZ_HTTP_TIMEOUT=15
# DIGIFLAZZ_HTTP_RETRY_TIMES=2
# DIGIFLAZZ_HTTP_RETRY_SLEEP_MS=200
```
Keep your API key out of source control. Use secure secrets handling in CI/CD.

## Security Considerations
- All endpoints use **POST** with `application/json`.
- **Signature rules**: MD5 over concatenation per Digiflazz docs (e.g., `username + apiKey + ref_id`).
- **Webhook** validation: HMAC‑SHA1 of raw body vs `X-Hub-Signature`. Set `DIGIFLAZZ_WEBHOOK_SECRET`.
- Digiflazz recommends whitelisting **`52.74.250.133`** on their side.
- Always log and audit your own transaction table; never rely on third‑party state alone.

## Response Codes → Exceptions
Selected mappings (non‑exhaustive):
- `00` success
- `03` → `PendingException`
- `01`, `70` → `TimeoutException`
- `40`, `57`, `84` → `ValidationException`
- `41` → `InvalidSignatureException`
- `44` → `InsufficientBalanceException`
- `49` → `DuplicateRefIdException`
- `50`, `60` → `NotFoundException`
- `53`, `68`, `55`, `71`, `56` → `ProductUnavailableException`
- `58`, `66` → `CutOffException`
- `83`, `85`, `86` → `RateLimitedException`
Any other RC → `DigiflazzException` including RC in the message.

---

## Usage

### Check Balance
```php
use Triyatna\DigiflazzBuyer\Facades\Digiflazz;

$balanceRaw = Digiflazz::checkBalance();
```

### Price List (Prepaid/Pasca)
```php
$prepaid = Digiflazz::priceList(['cmd' => 'prepaid']);
$pasca   = Digiflazz::priceList(['cmd' => 'pasca']);
```

### Deposit
```php
$deposit = Digiflazz::deposit([
  'amount' => 2000000,
  'bank' => 'BCA',
  'owner_name' => 'Your Name',
]);
```

### Prepaid Topup
Positional:
```php
$tx = Digiflazz::topupPrepaid('SKU123', '08123456789', 'INV-2025-0001');
```
Array:
```php
$tx = Digiflazz::topupPrepaid([
  'buyer_sku_code' => 'SKU123',
  'customer_no' => '08123456789',
  'ref_id' => 'INV-2025-0001',
  // 'max_price' => 15000,
  // 'testing' => true,
]);
```
If RC is not `00`, an exception is thrown per mapping above.

### Postpaid Inquiry
```php
$inq = Digiflazz::inquiryPostpaid([
  'buyer_sku_code' => 'PASCABPJS',
  'customer_no' => '000123456789',
  'ref_id' => 'INV-2025-0002',
]);
```

### Postpaid Payment
```php
$pay = Digiflazz::payPostpaid([
  'buyer_sku_code' => 'PASCABPJS',
  'customer_no' => '000123456789',
  'ref_id' => 'INV-2025-0002', // must be same as inquiry
]);
```

### Postpaid Status
```php
$status = Digiflazz::statusPostpaid([
  'buyer_sku_code' => 'PASCABPJS',
  'customer_no' => '000123456789',
  'ref_id' => 'INV-2025-0002',
]);
```

### PLN Inquiry
```php
$pln = Digiflazz::inquiryPln('12345678901');
```

---

## Webhook
Route:
```php
use Triyatna\DigiflazzBuyer\Http\Controllers\WebhookController;

Route::post('/digiflazz/webhook', WebhookController::class)->name('digiflazz.webhook');
```
- Set `DIGIFLAZZ_WEBHOOK_SECRET` to enable signature validation.
- Headers read: `X-Digiflazz-Event`, `X-Hub-Signature`.
- Controller returns `200 OK` when valid. Log payloads and act on events in your domain layer.

---

## DTOs
```php
use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClientInterface;

/** @var DigiflazzClientInterface $client */
$client = app(DigiflazzClientInterface::class);

$balanceDto = $client->checkBalanceDto();
// $balanceDto->deposit

$txDto = $client->topupPrepaidDto('SKU123', '08123456789', 'INV-2025-0001');
// $txDto->refId, $txDto->status, $txDto->rc, $txDto->message
```

---

## Caching & Performance
- **Price list**: cache by `cmd` and filters. Set a TTL suitable for your business (many sellers refresh periodically).
- **HTTP**: adjust `DIGIFLAZZ_HTTP_TIMEOUT`, `DIGIFLAZZ_HTTP_RETRY_*` for your environment.
- **Idempotency**: always generate unique `ref_id`. For prepaid status, repeat `transaction` with the **same** `ref_id` (Digiflazz pattern). For postpaid, use `status-pasca`.

---

## Testing & CI
- **Pest** tests included:
  - Signature generation
  - RC→exception mapping
  - DTO creation
  - Env installer insertion
- **GitHub Actions** workflow at `.github/workflows/ci.yml` runs Pest + PHPStan.
- Run locally:
```bash
composer install
composer test
composer analyse
```

---

## Troubleshooting
- *`Invalid signature`*: verify `DIGIFLAZZ_USERNAME`/`DIGIFLAZZ_API_KEY` and correct `sign` formula per endpoint.
- *`Ref ID not unique`*: ensure `ref_id` uniqueness in your system.
- *`Insufficient balance`*: top up seller balance or implement wallet checks before hitting API.
- *`Product unavailable` / `Cut off`*: catch exceptions and display user‑friendly messages; offer alternatives.
- *`Timeout/Pending`*: implement retry/backoff and delayed status checks as needed.

---

## Versioning & Support Matrix
- Package tracks Laravel LTS and current stable.
- Supported: Laravel **8, 9, 10, 11, 12**.
- PHP **^8.0** minimum.

---

## Sources
- Digiflazz Buyer API: preparation, cek saldo, price list, deposit, transaction (prepaid/postpaid), PLN inquiry, webhook, response codes.

---

## License
MIT


### Webhook Middleware
Add security by attaching the package middleware:
```php
use Illuminate\Support\Facades\Route;
use Triyatna\DigiflazzBuyer\Http\Controllers\WebhookController;

Route::post('/digiflazz/webhook', WebhookController::class)
    ->middleware('digiflazz.webhook')
    ->name('digiflazz.webhook');
```
It enforces IP whitelist (if configured) and `X-Hub-Signature` validation (when `DIGIFLAZZ_WEBHOOK_SECRET` is set).


## Flexible calls
You can call methods with `[]` or with no arguments when safe defaults exist:
- `Digiflazz::priceList()` or `Digiflazz::priceList([])` → defaults to `cmd=prepaid`
- Methods that **require** fields (e.g., `deposit`, `inquiryPostpaid`, `payPostpaid`, `statusPostpaid`, `topupPrepaid`) will throw a `ValidationException` if no required fields are passed.

Examples:
```php
$prepaid = Digiflazz::priceList();    // OK
$prepaid2 = Digiflazz::priceList([]); // OK

Digiflazz::deposit();                 // throws ValidationException
Digiflazz::inquiryPostpaid();         // throws ValidationException

Digiflazz::topupPrepaid('SKU', '081...', 'INV-1'); // OK
Digiflazz::topupPrepaid();                           // throws ValidationException
```


## Flexible calling styles
You can pass **arrays** or use **positional arguments**:

```php
// Price list
Digiflazz::priceList('prepaid', ['brand' => 'TELKOMSEL']);
Digiflazz::priceList(['cmd' => 'pasca', 'brand' => 'PLN']);

// Deposit
Digiflazz::deposit(2500000, 'BCA', 'Your Name');
Digiflazz::deposit(['amount' => 2500000, 'bank' => 'BCA', 'owner_name' => 'Your Name']);

// Prepaid
Digiflazz::topupPrepaid('SKU123', '08123456789', 'INV-1', ['max_price' => 15000]);
Digiflazz::topupPrepaid(['buyer_sku_code' => 'SKU123', 'customer_no' => '08123456789', 'ref_id' => 'INV-1']);

// Postpaid
Digiflazz::inquiryPostpaid('PASCABPJS', '000123456789', 'INV-2');
Digiflazz::payPostpaid('PASCABPJS', '000123456789', 'INV-2');
Digiflazz::statusPostpaid('PASCABPJS', '000123456789', 'INV-2');
```

