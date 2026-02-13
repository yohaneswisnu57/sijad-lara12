<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/login', function () {
    // dd(User::where('userid', '003130771')->first());
    return view('auth.login');
});
