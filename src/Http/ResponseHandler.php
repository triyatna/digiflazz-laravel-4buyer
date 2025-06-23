<?php

namespace Triyatna\Digiflazz\Http;

use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Arr;

class ResponseHandler
{
    /**
     * Daftar Response Code (RC) yang menandakan transaksi TERCATAT/TERBENTUK di sistem Digiflazz,
     * baik itu Sukses, Gagal, maupun Pending.
     */
    private const CREATED_TRANSACTION_RC = [
        '00',
        '01',
        '02',
        '03',
        '50',
        '51',
        '52',
        '53',
        '54',
        '55',
        '57',
        '58',
        '59',
        '60',
        '70',
        '71',
        '72',
        '73',
        '74',
        '84',
        '85',
        '86',
        '99'
    ];
    /**
     * @var array Data dari dalam key 'data' pada JSON response.
     */
    protected array $data;
    public function __construct(protected HttpResponse $response)
    {
        $this->data = $this->response->json('data', []);
    }

    public function data(): array
    {
        return $this->data;
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }


    public function isSuccess(): bool
    {
        if (!$this->has('rc')) {
            return true;
        }
        return $this->get('rc') === '00';
    }

    public function isPending(): bool
    {
        return in_array($this->get('rc'), ['03', '99']);
    }

    public function isFailed(): bool
    {
        if (!$this->has('rc')) {
            return false;
        }

        return !$this->isSuccess() && !$this->isPending();
    }

    public function transactionCreated(): bool
    {
        if (!$this->has('rc')) {
            return false;
        }

        return in_array($this->get('rc'), self::CREATED_TRANSACTION_RC);
    }

    public function getMessage(): ?string
    {
        return $this->get('message');
    }

    public function getResponseCode(): ?string
    {
        return $this->get('rc');
    }

    public function getOriginalResponse(): HttpResponse
    {
        return $this->response;
    }
}
