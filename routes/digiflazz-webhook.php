<?php

use Illuminate\Support\Facades\Route;
use Triyatna\DigiflazzBuyer\Http\Controllers\WebhookController;

Route::post('/digiflazz/webhook', WebhookController::class)->name('digiflazz.webhook');
