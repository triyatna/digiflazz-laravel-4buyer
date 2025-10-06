<?php

namespace Triyatna\DigiflazzBuyer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Triyatna\DigiflazzBuyer\Security\WebhookValidator;

class VerifyDigiflazzWebhook
{
    public function __construct(private WebhookValidator $validator) {}

    public function handle(Request $request, Closure $next)
    {
        $this->validator->validate($request);
        return $next($request);
    }
}
