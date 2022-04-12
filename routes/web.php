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
    Route::get('/', 'index')->name('index');
    Route::get('/domain-filename', 'domainFilename')->name('domain-filename');
    Route::get('/cached-routes', 'cachedRoutes')->name('cached-routes');
    Route::get('/cached-config', 'cachedConfig')->name('cached-config');
};

Route::controller(DomainController::class)
     ->domain('site1.test')
     ->name('site1.test.')
     ->group($routesDomain);

Route::controller(DomainController::class)
     ->domain('site2.test')
     ->name('site2.test.')
     ->group($routesDomain);

Route::get('/', [DomainController::class, 'index'])->name('home');
