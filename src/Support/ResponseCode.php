<?php

namespace Triyatna\DigiflazzBuyer\Support;

final class ResponseCode
{
    public const SUCCESS = '00';
    public const TIMEOUT = '01';
    public const FAILED = '02';
    public const PENDING = '03';
    public const PAYLOAD_ERROR = '40';
    public const INVALID_SIGNATURE = '41';
    public const BUYER_API_ERROR = '42';
    public const SKU_NOT_FOUND = '43';
    public const INSUFFICIENT_BALANCE = '44';
    public const UNKNOWN_IP = '45';
    public const DUPLICATE_BY_OTHER = '47';
    public const REF_ID_NOT_UNIQUE = '49';
    public const NOT_FOUND = '50';
    public const DEST_BLOCKED = '51';
    public const PREFIX_INVALID = '52';
    public const PRODUCT_UNAVAILABLE = '53';
    public const WRONG_DEST = '54';
    public const PRODUCT_ISSUE = '55';
    public const SELLER_LIMIT = '56';
    public const DIGIT_INVALID = '57';
    public const CUT_OFF = '58';
    public const OUT_OF_CLUSTER = '59';
    public const BILL_NOT_AVAILABLE = '60';
    public const NEVER_DEPOSITED = '61';
    public const SELLER_DOWN = '62';
    public const NO_MULTI_SUPPORT = '63';
    public const TICKET_PULL_FAILED = '64';
    public const MULTI_LIMIT = '65';
    public const SELLER_CUTOFF = '66';
    public const SELLER_UNVERIFIED = '67';
    public const STOCK_EMPTY = '68';
    public const SELLER_PRICE_HIGHER = '69';
    public const BILLER_TIMEOUT = '70';
    public const PRODUCT_UNSTABLE = '71';
    public const NEED_UNREG = '72';
    public const KWH_EXCEEDS = '73';
    public const REFUND = '74';
    public const BLOCKED_BY_SELLER = '80';
    public const SELLER_BLOCKED_BY_YOU = '81';
    public const UNVERIFIED_ACCOUNT = '82';
    public const PRICELIST_RATE_LIMIT = '83';
    public const NOMINAL_INVALID = '84';
    public const TX_RATE_LIMIT = '85';
    public const PLN_RATE_LIMIT = '86';
    public const ROUTER_ISSUE = '99';
}
