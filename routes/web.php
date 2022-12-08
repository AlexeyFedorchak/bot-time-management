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
        return redirect()->to(route('tasks'));
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
    $category = request()->category;
    $chatId = request()->worker;
    $status = request()->status;

    if (!empty($chatId)) {
        $taskIds = \App\Models\TaskUpdate::where('executor_id', $chatId)
            ->get('task_id')
            ->pluck('task_id')
            ->toArray();

        $tasks = \App\Models\Task::where(function ($query) use ($category) {
            $categoryValid = in_array($category, \App\Models\Task::categories());

            if (!empty($category) && $categoryValid) {
                $query->where('category', $category);
            }
        })
            ->whereIn('id', $taskIds)
            ->with(['author', 'updates'])
            ->orderBy('updated_at', 'DESC')
            ->get();

    } else {
        $tasks = \App\Models\Task::where(function ($query) use ($category) {
                $categoryValid = in_array($category, \App\Models\Task::categories());

                if (!empty($category) && $categoryValid) {
                    $query->where('category', $category);
                }
            })
            ->with(['author', 'updates'])
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    if ($status) {
        $newTaskList = [];

        foreach ($tasks as $task) {
            if ($task->updates->last()->status === $status) {
                $newTaskList[] = $task;
            }
        }

        $tasks = $newTaskList;
    }

    return view('tasks')->with(['tasks' => $tasks]);
})->name('tasks');

Route::get('/tasks/{task}', function (\App\Models\Task $task) {
    return view ('task')->with([
        'task' => $task->load(['updates' => function ($query){ $query->with('executor'); }]),
    ]);
})->name('tasks.show');

Route::get('/logout', function () {
    auth()->logout();

    return redirect()->to('/');
})->name('logout');

Route::get('/workers', function () {
    return view('workers');
})->name('workers');
