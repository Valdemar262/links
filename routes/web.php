<?php

use App\Http\Controllers\RedirectShortLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/cabinet');
});

Route::get('/{code}', RedirectShortLinkController::class)
    ->where('code', '[A-Za-z0-9]{6}')
    ->name('short-links.redirect');
