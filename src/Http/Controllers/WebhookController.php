<?php

namespace Triyatna\DigiflazzBuyer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    public function __invoke(Request $request): Response
    {
        $secret = (string) config('digiflazz.webhook_secret', '');
        if ($secret !== '') {
            $given = (string) $request->header('X-Hub-Signature', '');
            $calc  = 'sha1='.hash_hmac('sha1', $request->getContent(), $secret);
            if (!hash_equals($given, $calc)) {
                return new Response('Invalid signature', 401);
            }
        }
        $event = (string) $request->header('X-Digiflazz-Event', '');
        Log::info('Digiflazz webhook', ['event' => $event, 'payload' => $request->all()]);
        return new Response('OK', 200);
    }
}
