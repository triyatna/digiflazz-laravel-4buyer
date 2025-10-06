<?php

namespace Triyatna\DigiflazzBuyer\Security;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class WebhookValidator
{
    public function __construct(private Application $app) {}

    public function validate(Request $request): void
    {
        $this->assertIpAllowed($request);
        $this->assertSignatureValid($request);
    }

    public function assertIpAllowed(Request $request): void
    {
        $whitelist = (array) config('digiflazz.ip_whitelist', []);
        if (empty($whitelist)) return;
        $ip = $request->ip();
        foreach ($whitelist as $allowed) {
            if ($ip === $allowed) return;
        }
        abort(403, 'IP not allowed');
    }

    public function assertSignatureValid(Request $request): void
    {
        $secret = (string) config('digiflazz.webhook_secret', '');
        if ($secret === '') return;
        $given = (string) $request->header('X-Hub-Signature', '');
        $calc  = 'sha1='.hash_hmac('sha1', $request->getContent(), $secret);
        if (!hash_equals($given, $calc)) {
            abort(401, 'Invalid signature');
        }
    }
}
