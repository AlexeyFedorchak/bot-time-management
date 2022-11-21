<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    if (auth()->check()) {
        return view('tasks');
    } else {
        return view('login');
    }
});

Route::post('/login', function () {

    $user = \App\Models\User::where('email', request()->email)
        ->where('password', request()->password)
        ->first();

    if (!$user) {
        return view('login');
    }

    auth()->login($user);

    return redirect()->to(route('tasks'));
})->name('login');

Route::get('/tasks', function () {
    return view('tasks');
})->name('tasks');

Route::get('/tasks/{task}', function (\App\Models\Task $task) {
    return view ('task')->with([
        'task' => $task
    ]);
})->name('tasks.show');

Route::get('/logout', function () {
    auth()->logout();

    return redirect()->to('/');
})->name('logout');
