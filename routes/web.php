<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Fallback untuk file lama yang ada di production
// Jangan tangani route /storage/* di local/dev karena itu akan
// memblokir akses file publik dari symlink storage -> public/storage.
if (app()->environment('production')) {
    Route::get('/storage/{path}', function ($path) {
        return redirect("https://sitor-backend-production.up.railway.app/storage/" . $path);
    })->where('path', '.*');
}
