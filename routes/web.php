<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('users.index'));
Route::resource('users', UserController::class)->except('show');
