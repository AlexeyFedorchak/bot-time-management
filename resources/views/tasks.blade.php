<ol>
    @foreach(\App\Models\Task::all() as $task)
        <li>{{ $task->category }} | {{ $task->description }} | {{ $task->creator_id }} |
            <a href="{{ route('tasks.show', ['task' => $task->id]) }}">link</a>
        </li>
    @endforeach
</ol>
