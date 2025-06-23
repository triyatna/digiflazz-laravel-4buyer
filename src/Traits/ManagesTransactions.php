<?php

namespace Triyatna\Digiflazz\Traits;

use Triyatna\Digiflazz\Helpers\Signature;
use Triyatna\Digiflazz\Http\ResponseHandler;

trait ManagesTransactions
{
    /**
     * Membuat transaksi produk prabayar.
     *
     * @param string $productCode Kode produk dari Digiflazz.
     * @param string $customerNo Nomor pelanggan.
     * @param string $refId ID Referensi unik dari sistem.
     * @param bool $testing Set ke true untuk mode development tanpa potong saldo.
     * @return ResponseHandler
     */
    public function createPrepaidTransaction(string $productCode, string $customerNo, string $refId, bool $testing = false): ResponseHandler
    {
        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $productCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => Signature::generate($this->username, $this->apiKey, $refId),
        ];

        if ($testing) {
            $payload['testing'] = true;
        }

        return $this->client->post('/transaction', $payload);
    }

    /**
     * Melakukan inquiry (cek tagihan) untuk produk pascabayar.
     *  
     * @param string $productCode Kode produk dari Digiflazz.
     * @param string $customerNo Nomor pelanggan.
     * @param string $refId ID Referensi unik dari sistem.
     * @param bool $testing Set ke true untuk mode development.
     */
    public function checkPostpaidBill(string $productCode, string $customerNo, string $refId, bool $testing = false): ResponseHandler
    {
        $payload = [
            'commands' => 'inq-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $productCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => Signature::generate($this->username, $this->apiKey, $refId),
        ];

        if ($testing) {
            $payload['testing'] = true;
        }

        return $this->client->post('/transaction', $payload);
    }

    /**
     * Melakukan pembayaran tagihan pascabayar.
     *  
     * @param string $productCode Kode produk dari Digiflazz.
     * @param string $customerNo Nomor pelanggan.
     * @param string $refId ID Referensi unik dari sistem.
     * @param bool $testing Set ke true untuk mode development tanpa potong saldo.
     */
    public function payPostpaidBill(string $productCode, string $customerNo, string $refId, bool $testing = false): ResponseHandler
    {
        $payload = [
            'commands' => 'pay-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $productCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => Signature::generate($this->username, $this->apiKey, $refId),
        ];

        if ($testing) {
            $payload['testing'] = true;
        }

        return $this->client->post('/transaction', $payload);
    }

    /**
     * Cek status transaksi yang sudah ada.
     *  
     * @param string $refId ID Referensi unik dari sistem.
     * @param string $type Jenis transaksi ('prepaid' atau 'postpaid').
     * @param string $productCode Kode produk dari Digiflazz.
     * @param string $customerNo Nomor pelanggan.
     * @param bool $testing Set ke true untuk mode development.
     */
    public function checkTransactionStatus(string $refId, string $type, string $productCode, string $customerNo, bool $testing = false): ResponseHandler
    {
        if ($type === 'prepaid') {
            return $this->createPrepaidTransaction(
                $productCode,
                $customerNo,
                $refId,
                $testing
            );
        }

        $payload = [
            'commands' => 'status-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $productCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => Signature::generate($this->username, $this->apiKey, $refId),
        ];

        if ($testing) {
            $payload['testing'] = true;
        }

        return $this->client->post('/transaction', $payload);
    }
}
