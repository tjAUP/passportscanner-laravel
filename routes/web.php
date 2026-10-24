<?php

use Illuminate\Support\Facades\Route;
use TjAup\PassportScanner\Laravel\AssetController;

Route::get('/_passportscanner/assets/{path}', [AssetController::class, 'show'])
    ->where('path', '.*')
    ->name('passportscanner.asset');
