<?php

namespace App\Console\Commands;

use App\Models\LastOffset;
use App\Models\PendingChat;
use App\Models\RespondedMessage;
use App\Models\Task;
use App\Models\TaskUpdate;
use Illuminate\Console\Command;
use Telegram\Bot\Keyboard\Keyboard;

class Communicate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'communicate';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Communicate');

        $offset = LastOffset::first()->offset;
        $updates = app('Telegram')->getUpdates($offset, true);

        if (count($updates['result'])  > 0) {
            $newOffset = $updates['result'][count($updates['result']) - 1]['update_id'];
            $newOffset -= 50;

            if ($newOffset <= 0) {
                $newOffset = 1;
            }

            LastOffset::first()->update([
                'offset' => $newOffset
            ]);
        }

        foreach ($updates['result'] as $update) {
            if (!isset($update['message'])) {
                continue;
            }
            $message = $update['message'];

            if (!isset($message['chat']))
                continue;

            $chat = $message['chat'];

            if (!isset($message['text']) && !isset($message['caption']))
                continue;

            $messageResponded = RespondedMessage::where('chat_id', $chat['id'])
                ->where('message_id', $message['message_id'])
                ->exists();

            if ($messageResponded)
                continue;

            $pendingChat = PendingChat::where('chat_id', $chat['id'])->first();

            if ($pendingChat && $message['message_id'] > $pendingChat['message_id']) {
                $filteredUpdates = $this->getUpdatesByChatId($chat['id'], $updates['result']);

                $lastMessage = $filteredUpdates[count($filteredUpdates) - 1];
                $lastPreMessage = $filteredUpdates[count($filteredUpdates) - 2];

                if (!isset($lastMessage['text'])) {
                    $lastMessage['text'] = $lastMessage['caption'];
                }

                if (!isset($lastPreMessage['text'])) {
                    $lastPreMessage['text'] = $lastPreMessage['caption'];
                }

                 if ($lastPreMessage['message_id'] <= $pendingChat['message_id']) {
                     continue;

//                    $this->message($chat, 'Здається ви не вказали категорію або опис. Спробуйте ще раз.');
//                    $pendingChat->delete();
//                    continue;
                } else if (strlen($lastPreMessage['text']) < 5) {
                    $this->message($chat, 'Ваш опис занадто короткий. Спробуйте ще раз.');
                    $pendingChat->delete();
                    continue;
                } else if ($lastMessage['text'] == $lastPreMessage['text']) {
                    $this->message($chat, 'Назва категорії і опис завдання не можуть збігатись. Спробуйте ще раз.');
                    $pendingChat->delete();
                    continue;
                } else if (!in_array($lastMessage['text'], Task::categories())) {
                    $this->message($chat, 'Здається ви вказали неправильну категорію. Спробуйте ще раз.');
                    $pendingChat->delete();
                    continue;
                } else if (in_array($lastPreMessage['text'], Task::categories())) {
                    $this->message($chat, 'Опис завдання не може дорівнювати назві категорії. Спробуйте ще раз.');
                    $pendingChat->delete();
                    continue;
                }

                $this->createTask($lastMessage, $lastPreMessage, $chat);

                $pendingChat->delete();
                continue;
            }

            if (!isset($message['text'])) {
                $message['text'] = $message['caption'];
            }

            if ($chat['type'] === 'private' && $message['text'] === '+') {
                $this->sendKeyboard($chat, $message);
            }
        }
    }

    /**
     * @param string $chatId
     * @param array $updates
     * @return array
     */
    private function getUpdatesByChatId(string $chatId, array $updates): array
    {
        $filteredUpdates = [];

        foreach ($updates as $update) {
            if (!isset($update['message']['chat'])) {
                continue;
            }

            $foundChatId = $update['message']['chat']['id'];

            if ($chatId == $foundChatId) {
                $filteredUpdates[] = $update['message'];
            }
        }

        usort($filteredUpdates, function ($a, $b) {
            if ($a['message_id'] == $b['message_id']) {
                return 0;
            }

            return ($a['message_id'] < $b['message_id']) ? -1 : 1;
        });

        return $filteredUpdates;
    }

    /**
     * @param array $chat
     * @param array $message
     * @return void
     */
    private function sendKeyboard(array $chat, array $message): void
    {
        $keyboard = [
            [Task::CATEGORY_GIDRAVLIK], [Task::CATEGORY_MECHANIC], [Task::CATEGORY_ELECTRICITY],
        ];

        $replyMarkup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        app('Telegram')->getClient()->sendMessage([
            'chat_id' => $chat['id'],
            'text' => "Отже, ви хочете створити нове завдання?\r\nВкажіть опис та виберіть категорію.",
            'reply_markup' => $replyMarkup,
        ]);

        PendingChat::create([
            'chat_id' => $chat['id'],
            'message_id' => $message['message_id'],
        ]);

        RespondedMessage::create([
            'message_id' => $message['message_id'],
            'chat_id' => $chat['id'],
        ]);
    }

    /**
     * @param array $lastMessage
     * @param array $lastPreMessage
     * @param array $chat
     * @return void
     */
    private function createTask(array $lastMessage, array $lastPreMessage, array $chat): void
    {
        $existedTask = Task::where('category', $lastMessage['text'])
            ->where('description', $lastPreMessage['text'])
            ->first();

        if ($existedTask)
            if ($existedTask->getStatus() === TaskUpdate::STATUS_OPEN)
                return;

        $task = Task::create([
            'category' => $lastMessage['text'],
            'description' => $lastPreMessage['text'],
            'photo' => json_encode($lastPreMessage['photo'] ?? ''),
            'creator_id' => $chat['first_name'],
            'message_id' => null,
        ]);

        TaskUpdate::create([
            'executor_id' => $chat['first_name'],
            'task_id' => $task->id,
        ]);

        app('Telegram')->getClient()->sendMessage([
            'chat_id' => $chat['id'],
            'text' => "Завдання створено.\r\nКатегорія: {$task->category}.\r\nОпис: {$task->description}.",
        ]);

        if (!empty($lastPreMessage['photo'])) {
            $message = app('Telegram')->getClient()
                ->sendPhoto([
                    'chat_id' => $task->getChatIdByCategory(),
                    'photo' => $lastPreMessage['photo'][0]['file_id'],
                    'caption' => "Нове завдання.\r\nОпис: {$task->description}.",
                ]);
        } else {
            $message = app('Telegram')->getClient()->sendMessage([
                'chat_id' => $task->getChatIdByCategory(),
                'text' => "Нове завдання.\r\nОпис: {$task->description}.",
            ]);
        }

        $task->message_id = $message->toArray()['message_id'];
        $task->save();
    }

    /**
     * @param array $chat
     * @param string $message
     * @return void
     */
    private function message(array $chat, string $message): void
    {
        app('Telegram')->getClient()->sendMessage([
            'chat_id' => $chat['id'],
            'text' => $message,
        ]);
    }
}
