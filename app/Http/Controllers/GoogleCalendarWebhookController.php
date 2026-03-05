<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGoogleToLibra;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GoogleCalendarWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $state = $request->header('X-Goog-Resource-State');

        if ($state === 'sync') {
            return response('', 200);
        }

        if ($state === 'exists') {
            SyncGoogleToLibra::dispatch();
        }

        return response('', 200);
    }
}
