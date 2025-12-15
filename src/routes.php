<?php

use Illuminate\Support\Facades\Route;
use IndoPay\Http\Controllers\WebhookController;

Route::post('indopay/webhook/{driver}', [WebhookController::class, 'handle'])
    ->name('indopay.webhook');
