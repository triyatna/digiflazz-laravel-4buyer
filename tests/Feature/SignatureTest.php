<?php

use Triyatna\DigiflazzBuyer\Support\Signature;

it('generates balance signature', function () {
    expect(Signature::sign('user', 'key'))->toBe(md5('user'.'key'.'depo'));
});

it('generates ref signature', function () {
    expect(Signature::sign('user', 'key', 'REF1'))->toBe(md5('user'.'key'.'REF1'));
});

it('generates pln signature', function () {
    expect(Signature::sign('user', 'key', null, '123'))->toBe(md5('user'.'key'.'123'));
});
