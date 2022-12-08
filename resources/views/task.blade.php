<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<div class="alert alert-primary" role="alert" style="margin-left: 0px; display: flex">
    <div style="margin-left: 50px"><a href="{{ route('tasks') }}">Перейти до всіх завдань</a></div>
    <div style="margin-left: 50px"><a href="/logout">Вийти з системи</a></div>
</div>

<h2 class="alert alert-secondary" >
    <div style="margin-left: 50px">{{ \App\Helpers\Strings::limit($task->description, 42) }}</div>
</h2>
<div class="alert alert-info" >
    <div style="margin-left: 50px">Тривалість виконаня (секунд): <b>{{ \App\Helpers\Time::formatTimeInSeconds($task->getDuration() ?: 'Завдання проігноровано.') }}</b></div>
</div>

@if(!empty($task->photo) && $task->photo !== '""')
    <div style="margin-left: 50px">
        <img src="{{ app('Telegram')->getFileUrl($task) }}" alt="">
    </div>
    <br>
@endif


<table class="table">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Статус</th>
        <th scope="col">Автор/Виконавець</th>
        <th scope="col">Причина зміни статусу</th>
        <th scope="col">Час</th>
    </tr>
    </thead>
    <tbody>
    @foreach($task->updates as $key => $update)
        <tr>
            <th scope="row">{{ $key }}</th>
            <td>{{ $update->status }}</td>
            <td><a href="/tasks?worker={{ $update->executor->chat_id }}">{{ $update->executor->name_pib }}</a></td>
            <td>{{ $update->reason }}</td>
            <td>{{ $update->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
