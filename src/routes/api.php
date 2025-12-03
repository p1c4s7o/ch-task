<?php

use App\Http\Controllers\DomainController;
use App\Http\Controllers\NginxController;
use Illuminate\Support\Facades\Route;

// DomainController


Route::post('v{version}/domain/create', [DomainController::class, 'create'])
    ->whereNumber('version');

Route::delete('v{version}/domain/{domain}', [DomainController::class, 'delete'])
    ->whereNumber('version');

Route::match(['GET', 'HEAD'], 'v{version}/status/{domain}', [DomainController::class, 'status'])
    ->whereNumber('version');



// NginxController


Route::post('server/stop', [NginxController::class, 'stop']);

Route::post('server/start', [NginxController::class, 'start']);

Route::post('server/reload', [NginxController::class, 'reload']);

Route::get('server/status', [NginxController::class, 'status']);

Route::post('server/restart', [NginxController::class, 'restart']);
