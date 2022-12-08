<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<br>
<div style="margin-left: 50px; display: flex">
    @if (request()->has('category') || request()->has('worker'))
        <a href="{{ route('tasks') }}">Перейти до всіх завдань</a>
    @endif
    <div style="margin-left: 50px"><a href="{{ route('workers') }}">Перейти до працівників</a></div>
    <div style="margin-left: 50px"><a href="/logout">Вийти з системи</a></div>
</div>
<br>
<div style="margin-left: 50px; display: flex">
    @if (request()->has('worker'))
        Перегляд всіх завдань працівника: <b style="margin-left: 5px">{{ $tasks->first()->author->name_pib }}</b>
    @endif

    @if (request()->has('category'))
            Перегляд всіх завдань категорії: <b style="margin-left: 5px">{{ $tasks->first()->category }}</b>
    @endif
</div>

<br>
<table class="table">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Категорія</th>
        <th scope="col">Опис</th>
        @if (request()->has('worker'))
            <th scope="col">Автор/Виконавець</th>
        @else
            <th scope="col">Автор</th>
        @endif
        <th scope="col">Перейти на завдання</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tasks as $key => $task)

        <tr>
            <th scope="row">{{ $key }}</th>
            <td><a href="/tasks?category={{ $task->category }}">{{ $task->category }}</a></td>
            <td>{{ \App\Helpers\Strings::limit($task->description) }}</td>
            <td><a href="/tasks?worker={{ $task->author->chat_id }}">{{ $task->author->name_pib }}</a></td>
            <td><a href="{{ route('tasks.show', ['task' => $task->id]) }}">Детальніше</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
