<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', function(Request $request){
    $cred = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (!Auth::attempt($cred)) {
        return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 422);
    }

    $request->session()->regenerate();
    return response()->noContent();
});

Route::post('/logout', function(Request $request){
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->noContent();
});
