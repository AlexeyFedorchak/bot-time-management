<?php

namespace App\Console\Commands;

use App\Models\LastOffset;
use App\Models\Task;
use App\Models\TaskUpdate;
use Illuminate\Console\Command;

class TrackMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Track');

        $trackedMessages = Task::where('is_tracking', true)
            ->get('message_id')
            ->pluck('message_id')
            ->toArray();

        $offset = LastOffset::first()->offset;
        $updates = app('Telegram')->getUpdates($offset, true);

        foreach ($updates['result'] as $update) {
            if (!isset($update['message'])) {
                continue;
            }

            $message = $update['message'];

            if (!isset($message['reply_to_message'])) {
                continue;
            }

            $repliedTo = $message['reply_to_message'];

            if (!in_array($repliedTo['message_id'], $trackedMessages)) {
                continue;
            }

            if ($message['text'] === TaskUpdate::REPLY_IN_PROGRESS) {
                $task = Task::where('message_id', $repliedTo['message_id'])
                    ->first();

                if ($task->getStatus() === TaskUpdate::STATUS_IN_PROGRESS)
                    continue;

                TaskUpdate::create([
                    'task_id' => $task->id,
                    'status' => TaskUpdate::STATUS_IN_PROGRESS,
                    'executor_id' => $message['from']['first_name'],
                ]);
            } else if ($message['text'] === TaskUpdate::REPLY_DONE) {
                $task = Task::where('message_id', $repliedTo['message_id'])
                    ->first();

                $task->is_tracking = false;
                $task->save();

                TaskUpdate::create([
                    'task_id' => $task->id,
                    'status' => TaskUpdate::STATUS_DONE,
                    'executor_id' => $message['from']['first_name'],
                ]);
            } else if ($message['text'] === TaskUpdate::REPLY_CANCELLED) {
                $task = Task::where('message_id', $repliedTo['message_id'])
                    ->first();

                $task->is_tracking = false;
                $task->save();

                TaskUpdate::create([
                    'task_id' => $task->id,
                    'status' => TaskUpdate::STATUS_CANCELLED,
                    'executor_id' => $message['from']['first_name'],
                ]);
            }
        }
    }
}
