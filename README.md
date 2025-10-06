# digiflazz-laravel-4buyer (v2)

A modern, safe, and feature‑rich **Digiflazz Buyer API** client for **Laravel** Library.

---

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Why This Library](#why-this-library)
- [Comparison with v1](#comparison-with-v1)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Environment Variables](#environment-variables)
- [Usage](#usage)
  - [Check Balance](#check-balance)
  - [Price List](#price-list)
  - [Deposit](#deposit)
  - [Prepaid Topup](#prepaid-topup)
  - [Postpaid — Inquiry](#postpaid--inquiry)
  - [Postpaid — Payment](#postpaid--payment)
  - [Postpaid — Status](#postpaid--status)
  - [PLN Inquiry](#pln-inquiry)
  - [Webhook Verification (Validator + Middleware)](#webhook-verification-validator--middleware)
  - [DTOs](#dtos)
- [Response Codes & Exceptions](#response-codes--exceptions)
- [Advanced Usage](#advanced-usage)
  - [Flexible Calling Styles](#flexible-calling-styles)
  - [Dependency Injection (No Facade)](#dependency-injection-no-facade)
  - [Cache Price List](#cache-price-list)
  - [Custom HTTP Settings](#custom-http-settings)
  - [Testing & CI](#testing--ci)
- [Security Notes](#security-notes)
- [Performance Notes](#performance-notes)
- [Migration from v1](#migration-from-v1)
- [Contributors](#contributors)
- [License](#license)

---

## Introduction

`triyatna/digiflazz-laravel-4buyer` is a Laravel package that implements the **Digiflazz Buyer API** with strong safety defaults, explicit response‑code mapping, and clean developer ergonomics. It covers **Balance**, **Price List**, **Deposit**, **Prepaid Topup**, **Postpaid (Inquiry/Payment/Status)**, **PLN Inquiry**, and **Webhook verification**. It supports **Laravel** version 8+ (recommended 12+).

---

## Features

| Area       | Details                                                                                                                                                                                                                                     |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Core API   | Balance (`/v1/cek-saldo`), Price List (`/v1/price-list`), Deposit (`/v1/deposit`), Prepaid Topup (`/v1/transaction`), Postpaid Inquiry/Payment/Status (via `commands: inq-pasca, pay-pasca, status-pasca`), PLN Inquiry (`/v1/inquiry-pln`) |
| Ergonomics | **Array** and **positional arguments** for most calls, Facade **and** DI interface, DTOs for typed usage                                                                                                                                    |
| Safety     | RC→Exception mapping, MD5 signature helper (per spec), **Webhook Validator** (HMAC‑SHA1 `X‑Hub‑Signature` + IP allowlist) + middleware alias                                                                                                |
| HTTP       | Laravel `Http` client with **timeouts** and **retries**, JSON‑only headers                                                                                                                                                                  |
| Tooling    | CI (GitHub Actions), Pest tests (unit/feature), PHPStan                                                                                                                                                                                     |
| Docs       | Full README, **env installer** command, examples you can paste into your project                                                                                                                                                            |

---

## Why This Library

- **Safe by default**: timeouts, retries, strict JSON parsing, secure webhook validation.
- **Predictable**: exceptions are mapped from Digiflazz **RC** codes.
- **Ergonomic**: quick positional calls or full array payloads when you need control.
- **Production‑ready**: CI + tests + static analysis included.
- **Flexible**: Facade or DI; DTOs optional; fits Laravel 12 + Vue Starter Kits.

---

## Comparison with v1

| Area           | v1                                                                    | v2 (this package)                                                               |
| -------------- | --------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| API coverage   | Balance, Price List, Deposit, Prepaid, Postpaid (inq/pay/status), PLN | Same coverage                                                                   |
| Developer API  | Mainly positional + helpers                                           | **Array + Positional** on most methods                                          |
| Error Handling | Response flags/handler                                                | **RC→Exception mapping**: Pending/Timeout/Validation/CutOff/RateLimited/etc.    |
| DTOs           | –                                                                     | **BalanceDto**, **TransactionDto**, **PriceItemDto**                            |
| Webhook        | Basic signature sample                                                | **Middleware** `digiflazz.webhook` + **WebhookValidator** (IP allowlist + HMAC) |
| Config/ENV     | Basic                                                                 | Config publish + **env installer** (`digiflazz:install-env`)                    |
| Quality        | –                                                                     | **CI** (GitHub Actions), **Pest** tests, **PHPStan**                            |
| DI             | Mostly Facade                                                         | **Facade + Interface** for DI/testing                                           |

> Migrating from v1? Replace `createPrepaidTransaction()` with `topupPrepaid()`, and convert response‑flag checks into try/catch for specific exceptions.

---

## Requirements

- PHP: **^8.0**
- Laravel: **8, 9, 10, 11, 12**

---

## Installation

```bash
composer require triyatna/digiflazz-laravel-4buyer
php artisan vendor:publish --tag=digiflazz-config
php artisan digiflazz:install-env
```

---

## Configuration

`config/digiflazz.php` (published):

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

---

## Environment Variables

Run the installer to guarantee the keys exist (grouped with proper spacing):

```bash
php artisan digiflazz:install-env
```

**Required**

```
DIGIFLAZZ_USERNAME=
DIGIFLAZZ_API_KEY=
DIGIFLAZZ_WEBHOOK_SECRET=
```

**Optional overrides**

```
DIGIFLAZZ_BASE_URL=https://api.digiflazz.com/v1
DIGIFLAZZ_HTTP_TIMEOUT=15
DIGIFLAZZ_HTTP_RETRY_TIMES=2
DIGIFLAZZ_HTTP_RETRY_SLEEP_MS=200
```

---

## Usage

```php
use Triyatna\DigiflazzBuyer\Facades\Digiflazz;
```

### Check Balance

```php
$balanceRaw = Digiflazz::checkBalance();
$balanceDto = Digiflazz::checkBalanceDto(); // ->deposit, ->hold
```

### Price List

```php
// positional-like by string + filters
$prepaid = Digiflazz::priceList('prepaid', ['brand' => 'TELKOMSEL']);
// full array
$pasca   = Digiflazz::priceList(['cmd' => 'pasca', 'brand' => 'PLN']);
```

### Deposit

```php
// positional
$deposit = Digiflazz::deposit(1000000, 'BCA', 'Your Name');
// array
$deposit = Digiflazz::deposit([
  'amount'     => 1000000,
  'bank'       => 'BCA',
  'owner_name' => 'Your Name',
]);
```

### Prepaid Topup

```php
// positional
$tx = Digiflazz::topupPrepaid('SKU123', '08123456789', 'INV-2025-0001', [
  'max_price' => 15000,
  // 'testing' => true,
]);

// array
$tx = Digiflazz::topupPrepaid([
  'buyer_sku_code' => 'SKU123',
  'customer_no' => '08123456789',
  'ref_id' => 'INV-2025-0001',
]);
```

### Postpaid — Inquiry

```php
// positional
$inq = Digiflazz::inquiryPostpaid('PASCABPJS', '000123456789', 'INV-2025-0002');

// array
$inq = Digiflazz::inquiryPostpaid([
  'buyer_sku_code' => 'PASCABPJS',
  'customer_no' => '000123456789',
  'ref_id' => 'INV-2025-0002',
]);
```

### Postpaid — Payment

```php
$pay = Digiflazz::payPostpaid('PASCABPJS', '000123456789', 'INV-2025-0002');
// or
$pay = Digiflazz::payPostpaid([
  'buyer_sku_code' => 'PASCABPJS',
  'customer_no' => '000123456789',
  'ref_id' => 'INV-2025-0002',
]);
```

### Postpaid — Status

```php
$status = Digiflazz::statusPostpaid('PASCABPJS', '000123456789', 'INV-2025-0002');
// or
$status = Digiflazz::statusPostpaid([
  'buyer_sku_code' => 'PASCABPJS',
  'customer_no' => '000123456789',
  'ref_id' => 'INV-2025-0002',
]);
```

### PLN Inquiry

```php
$pln = Digiflazz::inquiryPln('12345678901');               // positional
$pln = Digiflazz::inquiryPln(['customer_no' => '123...']); // array
```

### Webhook Verification (Validator + Middleware)

The package ships a **validator** and a **middleware alias** to secure your webhook endpoint with **IP allowlist** and **HMAC‑SHA1 signature** checks.

**1) Route with middleware**

```php
use Illuminate\Support\Facades\Route;
use Triyatna\DigiflazzBuyer\Http\Controllers\WebhookController;

Route::post('/digiflazz/webhook', WebhookController::class)
    ->middleware('digiflazz.webhook') // IP whitelist + X-Hub-Signature HMAC verification
    ->name('digiflazz.webhook');
```

**2) Configure security**
`config/digiflazz.php`

```php
'ip_whitelist'   => ['52.74.250.133'], // [] to disable IP check
'webhook_secret' => env('DIGIFLAZZ_WEBHOOK_SECRET', ''),
```

`.env`

```
DIGIFLAZZ_WEBHOOK_SECRET=your_webhook_secret
```

**3) Custom controller (optional)**

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DigiflazzWebhookController
{
    public function __invoke(Request $request): Response
    {
        // Passed IP + signature checks by middleware
        $event   = $request->header('X-DGflazz-Event', $request->header('X-Digiflazz-Event', ''));
        $payload = $request->all();

        $ref     = data_get($payload, 'data.ref_id');
        $status  = data_get($payload, 'data.status');  // Sukses/Gagal/Pending
        $rc      = data_get($payload, 'data.rc');
        $message = data_get($payload, 'data.message');

        // Update your transaction here...
        return new Response('OK', 200);
    }
}
```

Route with the same middleware:

```php
Route::post('/digiflazz/webhook', \App\Http\Controllers\DigiflazzWebhookController::class)
    ->middleware('digiflazz.webhook')
    ->name('digiflazz.webhook');
```

**4) Troubleshooting & tips**

- Ensure **Trusted Proxies** are configured if you run behind a reverse proxy so `request()->ip()` is correct.
- Log `request()->getContent()` and the headers for failed validations to debug mismatches.
- Keep the route outside CSRF/session middleware groups.

### DTOs

```php
use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClientInterface;

/** @var DigiflazzClientInterface $client */
$client = app(DigiflazzClientInterface::class);

$balance = $client->checkBalanceDto(); // BalanceDto
$tx      = $client->topupPrepaidDto('SKU123', '08123456789', 'INV-2025-0001'); // TransactionDto
```

---

## Response Codes & Exceptions

Common mappings (non‑exhaustive):

|       RC | Message                      | Throws                         | Transaction Created |
| -------: | ---------------------------- | ------------------------------ | :-----------------: |
|       00 | Transaksi Sukses             | –                              |          ✔          |
|       01 | Timeout                      | `TimeoutException`             |          ✔          |
|       03 | Transaksi Pending            | `PendingException`             |          ✔          |
|       40 | Payload Error                | `ValidationException`          |          ✖          |
|       41 | Signature tidak valid        | `InvalidSignatureException`    |          ✖          |
|       44 | Saldo tidak cukup            | `InsufficientBalanceException` |          ✖          |
|       49 | Ref ID tidak unik            | `DuplicateRefIdException`      |          ✖          |
|       50 | Transaksi Tidak Ditemukan    | `NotFoundException`            |          ✔          |
|       53 | Produk Seller Tidak Tersedia | `ProductUnavailableException`  |          ✔          |
|       58 | Sedang Cut Off               | `CutOffException`              |          ✔          |
|       70 | Timeout dari Biller          | `TimeoutException`             |          ✔          |
| 83/85/86 | Rate limiting                | `RateLimitedException`         |          ±          |

Any other code → `DigiflazzException` with original message + RC.

---

## Advanced Usage

### Flexible Calling Styles

```php
// Price list
Digiflazz::priceList('prepaid', ['brand' => 'TELKOMSEL']);
Digiflazz::priceList(['cmd' => 'pasca', 'brand' => 'PLN']);

// Deposit
Digiflazz::deposit(2500000, 'BCA', 'Your Name');
Digiflazz::deposit(['amount' => 2500000, 'bank' => 'BCA', 'owner_name' => 'Your Name']);
```

### Dependency Injection (No Facade)

```php
namespace App\Services;

use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClientInterface;

class TopupService
{
    public function __construct(private DigiflazzClientInterface $client) {}

    public function topup(string $sku, string $msisdn, string $ref, array $options = []): array
    {
        return $this->client->topupPrepaid($sku, $msisdn, $ref, $options);
    }
}
```

### Cache Price List

```php
use Illuminate\Support\Facades\Cache;
use Triyatna\DigiflazzBuyer\Facades\Digiflazz;

$prices = Cache::remember('digiflazz:pricelist:prepaid', 300, function () {
    return Digiflazz::priceList('prepaid');
});
```

### Custom HTTP Settings

`.env`

```
DIGIFLAZZ_HTTP_TIMEOUT=20
DIGIFLAZZ_HTTP_RETRY_TIMES=3
DIGIFLAZZ_HTTP_RETRY_SLEEP_MS=300
```

### Testing & CI

- Run tests: `composer test`
- Static analysis: `composer analyse`
- CI workflow: `.github/workflows/ci.yml` (Pest + PHPStan)

---

## Security Notes

- Keep `DIGIFLAZZ_API_KEY` and `DIGIFLAZZ_WEBHOOK_SECRET` private.
- Use the middleware `digiflazz.webhook` to ensure signature + IP checks.
- Consider restricting webhook routes at your reverse proxy as well.

## Performance Notes

- Short timeouts + retries improve resilience.
- Cache `price-list` results.
- Use queues for heavy post‑processing after webhook events.

---

## Migration from v1

| v1 Call                                                     | v2 Equivalent                                                                           |
| ----------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| `createPrepaidTransaction($sku, $msisdn, $ref, $opts = [])` | `topupPrepaid($sku, $msisdn, $ref, $opts)`                                              |
| `getPriceList('prepaid', $filters = [])`                    | `priceList('prepaid', $filters)`                                                        |
| `checkTransactionStatus([...])`                             | `statusPostpaid([...])` (postpaid) or re‑hit `transaction` with same `ref_id` (prepaid) |
| Webhook sample with manual checks                           | Route middleware `digiflazz.webhook` + `WebhookValidator`                               |

Change response handling from a flags‑style object to **try/catch** using the mapped exceptions.

---

## Contributors

- **[@triyatna](https://github.com/triyatna)** — creator & maintainer
  Contributions are welcome. Please open issues with clear repro steps and propose focused PRs. Don't forget to star and fork

---

## License

This package is released under the [MIT License](LICENSE).
