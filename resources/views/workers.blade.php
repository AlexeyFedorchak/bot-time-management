<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<br>
<div style="margin-left: 50px; display: flex">
    <div style="margin-left: 50px"><a href="/logout">Вийти з системи</a></div>
</div>
<br>

<table class="table">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">ПІБ</th>
        <th scope="col">Телеграм Імя</th>
        <th scope="col">Перейти до завдань</th>
    </tr>
    </thead>
    <tbody>
    @foreach(\App\Models\RegisterRequest::all() as $key => $worker)

        <tr>
            <th scope="row">{{ $key }}</th>
            <td>{{ $worker->name_pib }}</td>
            <td>{{ $worker->telegram_first_name }} {{ $worker->telegram_last_name }}</td>
            <td><a href="/tasks?worker={{ $worker->chat_id }}">Детальніше</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
