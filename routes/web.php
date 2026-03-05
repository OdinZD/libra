<?php

use App\Http\Controllers\ReportController;
use App\Models\Workshop;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $workshops = Workshop::orderBy('sort_order')->get();
    return view('welcome', compact('workshops'));
})->name('home');

Route::get('reports/monthly-pdf', [ReportController::class, 'monthlyPdf'])
    ->middleware(['auth', 'verified'])
    ->name('reports.monthly-pdf');

Route::livewire('dashboard', 'pages::dashboard.calendar')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('dashboard/radionice', 'pages::dashboard.radionice')
    ->middleware(['auth', 'verified'])
    ->name('dashboard.radionice');

require __DIR__.'/settings.php';
