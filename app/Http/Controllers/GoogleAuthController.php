<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    public function redirect(GoogleCalendarService $service): RedirectResponse
    {
        return redirect()->away($service->getAuthUrl());
    }

    public function callback(Request $request, GoogleCalendarService $service): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('dashboard')->with('error', 'Google Calendar povezivanje otkazano.');
        }

        $service->handleAuthCallback($request->get('code'));

        return redirect()->route('dashboard')->with('success', 'Google Calendar uspješno povezan!');
    }
}
