<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

Route::post('/upload', [UploadController::class, 'upload']);
Route::get('/status', [UploadController::class, 'status']);