# Laravel Digiflazz Buyer API Package

Paket Laravel untuk menghubungkan aplikasi Anda dengan [API Digiflazz](https://digiflazz.com) sebagai **Buyer**.

## Ringkasan

Paket ini menyederhanakan integrasi API Digiflazz ke dalam Laravel, menyediakan fungsi-fungsi siap pakai yang mudah digunakan.

## Fitur

- **Implementasi Lengkap**: Semua fungsi dari dokumentasi Digiflazz Buyer tersedia.
- **Instalasi Cepat**: Artisan command untuk konfigurasi otomatis.
- **Penggunaan Mudah**: Panggil dengan `Digiflazz::namaFungsi()`.
- **Respons Terstruktur**: Response dibungkus oleh `ResponseHandler`.
- **Mode Uji Coba**: Testing tanpa memotong saldo.
- **Keamanan**: Validasi signature Webhook.

---

## Instalasi dan Konfigurasi

### 1. Instalasi Paket

```bash
composer require triyatna/digiflazz-laravel-4buyer
```

### 2. Setup Otomatis

```bash
php artisan ty-digiflazz:install
```

Perintah ini akan:

- Membuat file `config/digiflazz.php`
- Menambahkan entri ke file `.env`

### 3. Konfigurasi `.env`

```env
DIGIFLAZZ_USERNAME=masukkan_username_api_anda
DIGIFLAZZ_API_KEY=masukkan_api_key_anda
DIGIFLAZZ_WEBHOOK_SECRET=masukkan_secret_webhook_anda
```

> **Penting**: Pastikan IP server Anda didaftarkan di [panel Digiflazz](https://member.digiflazz.com/buyer-area/connection/api) bagian _Atur Koneksi > API_.
> **Informasi**: Dapatkan informasi Webhook Secret dan tambahkan url payload di [panel Digiflazz](https://member.digiflazz.com/buyer-area/connection/api) bagian _Atur Koneksi > API_ > Atur > Webhook.

---

## Panduan Penggunaan

### Konsep Dasar

Semua fungsi dipanggil via Facade `Digiflazz::...` dan menghasilkan instance `ResponseHandler`.

#### Fungsi ResponseHandler:

| Fungsi                 | Deskripsi                  |
| ---------------------- | -------------------------- |
| `isSuccess()`          | Transaksi sukses           |
| `isPending()`          | Transaksi diproses         |
| `isFailed()`           | Transaksi gagal            |
| `transactionCreated()` | Transaksi tercatat         |
| `data()`               | Mengembalikan seluruh data |
| `get('nama_key')`      | Mengambil 1 field          |
| `getMessage()`         | Mengembalikan pesan        |

---

## Contoh Penggunaan

Tambahkan ini di awal controller Anda:

```php
use Triyatna\Digiflazz\Digiflazz;
use Illuminate\Support\Str;
```

### 1. Manajemen Akun

#### a. Cek Saldo

```php
$response = Digiflazz::checkBalance();

if ($response->isSuccess()) {
    return $response->get('deposit');
}
```

#### b. Tiket Deposit

```php
$response = Digiflazz::requestDeposit(50000, 'BCA', 'Budi Santoso');

if ($response->isSuccess()) {
    return [
        'jumlah' => $response->get('amount'),
        'berita' => $response->get('notes'),
    ];
}
```

---

### 2. Informasi Produk

#### a. Daftar Harga

```php
$response = Digiflazz::getPriceList('prepaid', ['brand' => 'TELKOMSEL']);

if ($response->isSuccess()) {
    return $response->data();
}
```

#### b. Cek Nama Pelanggan PLN

```php
$response = Digiflazz::inquiryPln('1234554321');

if ($response->isSuccess()) {
    return $response->get('name');
}
```

---

### 3. Transaksi

#### a. Prabayar

```php
$refId = 'TRX-' . Str::uuid();

$response = Digiflazz::createPrepaidTransaction('tsel5', '081234567890', $refId, true);

if ($response->isSuccess()) {
    // sukses
}
```

#### b. Pascabayar

##### 1) Cek Tagihan

```php
$refId = 'INQ-' . Str::uuid();

$response = Digiflazz::checkPostpaidBill('pln', '530000000001', $refId, true);
```

##### 2) Bayar Tagihan

```php
$response = Digiflazz::payPostpaidBill('pln', '530000000001', $refId, true);
```

#### c. Cek Status Transaksi

```php
$response = Digiflazz::checkTransactionStatus(
    'TRX-xxxxxxxx-xxxx',
    'prepaid',
    'tsel5',
    '081234567890'
);

if ($response->isSuccess()) {
    // status sukses
}
```

---

## Webhook

### 1. Route

```php
use App\Http\Controllers\WebhookController;

Route::post('/webhooks/digiflazz', [WebhookController::class, 'handle']);
```

### 2. Controller

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Triyatna\Digiflazz\Helpers\Webhook;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Hub-Signature');
        $payload = $request->getContent();
        $secret = config('digiflazz.webhook_secret');

        if (!Webhook::validate($signature, $payload, $secret)) {
            return response('Validasi gagal.', 403);
        }

        $data = $request->input('data');
        Log::info("Webhook diterima untuk ref_id {$data['ref_id']} dengan status {$data['status']}");

        return response('Webhook diterima.', 200);
    }
}
```

---

## 🤝 Kontribusi

Kontribusi sangat disambut! Silakan buka issue atau pull request untuk:

- Laporan bug
- Perbaikan dokumentasi

---

## 📄 Lisensi

Paket ini dirilis di bawah [MIT License](LICENSE).

---

## 🧷 Penulis

Dikembangkan oleh [Triyatna](https://github.com/triyatna).
