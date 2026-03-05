<?php

use App\Http\Controllers\GoogleCalendarWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/google-calendar', [GoogleCalendarWebhookController::class, 'handle']);
