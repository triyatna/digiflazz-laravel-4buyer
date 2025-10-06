<?php

namespace Triyatna\DigiflazzBuyer\Http\Client;

use Illuminate\Support\Facades\Http;
use Triyatna\DigiflazzBuyer\Exceptions\{ApiErrorException, DigiflazzException, DuplicateRefIdException, InsufficientBalanceException, InvalidSignatureException, NotFoundException, PendingException, ProductUnavailableException, TimeoutException, ValidationException, CutOffException, RateLimitedException};
use Triyatna\DigiflazzBuyer\Support\ResponseCode;
use Triyatna\DigiflazzBuyer\Support\Signature;
use Triyatna\DigiflazzBuyer\Support\ResponseMapper;
use Triyatna\DigiflazzBuyer\DTO\{BalanceDto, TransactionDto};

class DigiflazzClient implements DigiflazzClientInterface
{
    public function __construct(
        private string $baseUrl,
        private string $username,
        private string $apiKey,
        private int $timeout = 15,
        private int $retryTimes = 2,
        private int $retrySleepMs = 200,
    ) {}

    protected function http()
    {
        return Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleepMs / 1000);
    }

    protected function post(string $path, array $payload): array
    {
        $res = $this->http()->post(rtrim($this->baseUrl, '/').'/'.ltrim($path, '/'), $payload);
        if (!$res->successful()) {
            throw new ApiErrorException('HTTP '.$res->status().': '.$res->body());
        }
        $json = $res->json();
        if (!is_array($json)) {
            throw new ApiErrorException('Invalid JSON response');
        }
        return $json;
    }

    protected function handleRc(array $json): array
    {
        $rc = (string) data_get($json, 'data.rc', data_get($json, 'rc'));
        $message = (string) data_get($json, 'data.message', data_get($json, 'message', ''));

        switch ($rc) {
            case ResponseCode::SUCCESS:
                return $json;
            case ResponseCode::PENDING:
                throw new PendingException($message ?: 'Pending');
            case ResponseCode::TIMEOUT:
            case ResponseCode::BILLER_TIMEOUT:
                throw new TimeoutException($message ?: 'Timeout');
            case ResponseCode::PAYLOAD_ERROR:
            case ResponseCode::DIGIT_INVALID:
            case ResponseCode::NOMINAL_INVALID:
                throw new ValidationException($message ?: 'Payload error');
            case ResponseCode::INVALID_SIGNATURE:
                throw new InvalidSignatureException($message ?: 'Invalid signature');
            case ResponseCode::INSUFFICIENT_BALANCE:
                throw new InsufficientBalanceException($message ?: 'Insufficient balance');
            case ResponseCode::REF_ID_NOT_UNIQUE:
                throw new DuplicateRefIdException($message ?: 'Ref ID not unique');
            case ResponseCode::PRODUCT_UNAVAILABLE:
            case ResponseCode::STOCK_EMPTY:
            case ResponseCode::PRODUCT_ISSUE:
            case ResponseCode::PRODUCT_UNSTABLE:
            case ResponseCode::SELLER_LIMIT:
                throw new ProductUnavailableException($message ?: 'Product unavailable');
            case ResponseCode::CUT_OFF:
            case ResponseCode::SELLER_CUTOFF:
                throw new CutOffException($message ?: 'Cut off');
            case ResponseCode::PRICELIST_RATE_LIMIT:
            case ResponseCode::TX_RATE_LIMIT:
            case ResponseCode::PLN_RATE_LIMIT:
                throw new RateLimitedException($message ?: 'Rate limited');
            case ResponseCode::NOT_FOUND:
            case ResponseCode::BILL_NOT_AVAILABLE:
                throw new NotFoundException($message ?: 'Not found');
            default:
                throw new DigiflazzException(($message ?: 'Transaction failed').' (RC '.$rc.')');
        }
    }

    public function checkBalance(): array
    {
        $payload = [
            'cmd' => 'deposit',
            'username' => $this->username,
            'sign' => Signature::sign($this->username, $this->apiKey),
        ];
        return $this->post('cek-saldo', $payload);
    }

    public function checkBalanceDto(): BalanceDto
    {
        return ResponseMapper::balance($this->checkBalance());
    }

    public function priceList(array|string $payload = [], array $filters = []): array
    {
        if (is_string($payload)) {
            $payload = array_merge(['cmd' => $payload], $filters);
        }
        $cmd = data_get($payload, 'cmd', 'prepaid');
        $body = array_merge([
            'cmd' => $cmd,
            'username' => $this->username,
            'sign' => md5($this->username.$this->apiKey.'pricelist'),
        ], $payload);

        return $this->post('price-list', $body);
    }

    public function deposit(array|int $payload, ?string $bank = null, ?string $ownerName = null, array $extra = []): array
    {
        if (is_int($payload)) {
            $payload = ['amount' => $payload, 'bank' => (string) $bank, 'owner_name' => (string) $ownerName] + $extra;
        }

        $body = array_merge([
            'username' => $this->username,
            'sign' => md5($this->username.$this->apiKey.'deposit'),
        ], $payload);

        return $this->post('deposit', $body);
    }

    public function topupPrepaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array
    {
        if (is_string($payload)) {
            $payload = [
                'buyer_sku_code' => $payload,
                'customer_no' => (string) $customerNo,
                'ref_id' => (string) $refId,
            ] + $extra;
        }

        $ref = (string) data_get($payload, 'ref_id');
        if ($ref === '') {
            throw new ValidationException('ref_id is required');
        }

        $body = array_merge([
            'username' => $this->username,
            'sign' => Signature::sign($this->username, $this->apiKey, $ref, null),
        ], $payload);

        $json = $this->post('transaction', $body);
        return $this->handleRc($json);
    }

    public function topupPrepaidDto(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): TransactionDto
    {
        return ResponseMapper::transaction($this->topupPrepaid($payload, $customerNo, $refId, $extra));
    }

    public function inquiryPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array
    {
        if (is_string($payload)) {
            $payload = [
                'buyer_sku_code' => $payload,
                'customer_no' => (string) $customerNo,
                'ref_id' => (string) $refId,
            ] + $extra;
        }
        $ref = (string) data_get($payload, 'ref_id');
        if ($ref === '') {
            throw new ValidationException('ref_id is required');
        }
        $body = array_merge([
            'commands' => 'inq-pasca',
            'username' => $this->username,
            'sign' => Signature::sign($this->username, $this->apiKey, $ref, null),
        ], $payload);

        $json = $this->post('transaction', $body);
        return $this->handleRc($json);
    }

    public function payPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array
    {
        if (is_string($payload)) {
            $payload = [
                'buyer_sku_code' => $payload,
                'customer_no' => (string) $customerNo,
                'ref_id' => (string) $refId,
            ] + $extra;
        }
        $ref = (string) data_get($payload, 'ref_id');
        if ($ref === '') {
            throw new ValidationException('ref_id is required');
        }
        $body = array_merge([
            'commands' => 'pay-pasca',
            'username' => $this->username,
            'sign' => Signature::sign($this->username, $this->apiKey, $ref, null),
        ], $payload);

        $json = $this->post('transaction', $body);
        return $this->handleRc($json);
    }

    public function statusPostpaid(array|string $payload, ?string $customerNo = null, ?string $refId = null, array $extra = []): array
    {
        if (is_string($payload)) {
            $payload = [
                'buyer_sku_code' => $payload,
                'customer_no' => (string) $customerNo,
                'ref_id' => (string) $refId,
            ] + $extra;
        }
        $ref = (string) data_get($payload, 'ref_id');
        if ($ref === '') {
            throw new ValidationException('ref_id is required');
        }
        $body = array_merge([
            'commands' => 'status-pasca',
            'username' => $this->username,
            'sign' => Signature::sign($this->username, $this->apiKey, $ref, null),
        ], $payload);

        return $this->post('transaction', $body);
    }

    public function inquiryPln(array|string $customerNoOrPayload): array
    {
        $payload = is_array($customerNoOrPayload)
            ? $customerNoOrPayload
            : ['customer_no' => (string) $customerNoOrPayload];

        $customerNo = (string) data_get($payload, 'customer_no', '');
        if ($customerNo === '') {
            throw new ValidationException('customer_no is required');
        }

        $body = [
            'username' => $this->username,
            'customer_no' => $customerNo,
            'sign' => Signature::sign($this->username, $this->apiKey, null, $customerNo),
        ];

        return $this->post('inquiry-pln', $body);
    }
}
