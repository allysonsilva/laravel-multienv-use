<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$routesDomain = function () {
    Route::get('/', [DomainController::class, 'index'])->name('index');
    Route::get('/domain-filename', [DomainController::class, 'domainFilename'])->name('domain-filename');
    Route::get('/cached-routes', [DomainController::class, 'cachedRoutes'])->name('cached-routes');
    Route::get('/cached-config', [DomainController::class, 'cachedConfig'])->name('cached-config');
};

Route::domain('site1.test')->name('site1.test.')->group(function () use ($routesDomain) {
    $routesDomain();
});

Route::domain('site2.test')->name('site2.test.')->group(function () use ($routesDomain) {
    $routesDomain();
});

Route::get('/', [DomainController::class, 'index'])->name('home');
