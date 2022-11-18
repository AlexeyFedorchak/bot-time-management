<h2>{{ $task->description }}</h2>
<h3>Тривалість виконаня (секунд): {{ $task->getDuration() ?: 'Завдання проігноровано.' }}</h3>
<ol>
    @foreach($task->updates as $update)
        <li>{{ $update->status }} | {{ $update->executor_id }} | час: {{ $update->created_at }}</li>
    @endforeach
</ol>
