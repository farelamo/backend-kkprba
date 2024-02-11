<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\RegulationController;
use App\Http\Controllers\RegulationCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::put('total-viewer', [DashboardController::class, 'update']);

// Route::middleware('auth.api')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::get('blog-carousel', [BlogController::class, 'carousel']);
    Route::resource('blog', BlogController::class);
    Route::resource('blog-category', BlogCategoryController::class);
    Route::resource('regulation', RegulationController::class);
    Route::resource('regulation-category', RegulationCategoryController::class);
// });