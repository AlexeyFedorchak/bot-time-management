<?php

namespace App\Console\Commands;

use App\Models\LastOffset;
use App\Models\PendingChat;
use App\Models\RespondedMessage;
use App\Models\Task;
use Illuminate\Console\Command;
use Telegram\Bot\Keyboard\Keyboard;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dd';

    /**
     * @TODO refactor, fix offset
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $offset = LastOffset::first()->offset;
        $updates = app('Telegram')->getUpdates(647448667, true);

        LastOffset::first()->update([
            'offset' => $updates['result'][count($updates['result']) - 1]['update_id']
        ]);

        foreach ($updates['result'] as $update) {
            if (!isset($update['message'])) {
                continue;
            }
            $message = $update['message'];

            if (!isset($message['chat']))
                continue;

            $chat = $message['chat'];

            $messageResponded = RespondedMessage::where('chat_id', $chat['id'])
                ->where('message_id', $message['message_id'])
                ->exists();

            if ($messageResponded)
                continue;

            $pendingChat = PendingChat::where('chat_id', $chat['id'])->first();

            if ($pendingChat && $message['message_id'] > $pendingChat['message_id']) {
                $filteredUpdates = $this->getUpdatesByChatId($chat['id'], $updates['result']);

                $lastMessage = $filteredUpdates[count($filteredUpdates) - 1]['message'];
                $lastPreMessage = $filteredUpdates[count($filteredUpdates) - 2]['message'];

                if (now()->diffInSeconds($pendingChat->created_at) <= 1) {
                    // wait... let's user think...
                    continue;
                } else if ($lastPreMessage['message_id'] <= $pendingChat['message_id']) {
                    app('Telegram')->getClient()->sendMessage([
                        'chat_id' => $chat['id'],
                        'text' => "Здається ви не вказали категорію або опис. Спробуйте ще раз.",
                    ]);

                    $pendingChat->delete();
                    continue;
                } else if (strlen($lastPreMessage['text']) < 5) {
                    app('Telegram')->getClient()->sendMessage([
                        'chat_id' => $chat['id'],
                        'text' => "Ваш опис занадто короткий. Спробуйте ще раз.",
                    ]);

                    $pendingChat->delete();
                    continue;
                } else if ($lastMessage['text'] == $lastPreMessage['text']) {
                    app('Telegram')->getClient()->sendMessage([
                        'chat_id' => $chat['id'],
                        'text' => "Назва категорії і опис завдання не можуть збігатись. Спробуйте ще раз.",
                    ]);

                    $pendingChat->delete();
                    continue;
                } else if (!in_array($lastMessage['text'], Task::categories())) {
                    app('Telegram')->getClient()->sendMessage([
                        'chat_id' => $chat['id'],
                        'text' => "Здається ви вказали неправильну категорію. Спробуйте ще раз.",
                    ]);

                    $pendingChat->delete();
                    continue;
                } else if (in_array($lastPreMessage['text'], Task::categories())) {
                    app('Telegram')->getClient()->sendMessage([
                        'chat_id' => $chat['id'],
                        'text' => "Опис завдання не може дорівнювати назві категорії. Спробуйте ще раз.",
                    ]);

                    $pendingChat->delete();
                    continue;
                }

                $task = Task::create([
                    'category' => $lastMessage['text'],
                    'description' => $lastPreMessage['text'],
                    'creator_id' => $chat['id'],
                ]);

                app('Telegram')->getClient()->sendMessage([
                    'chat_id' => $chat['id'],
                    'text' => "Завдання створено.\r\nКатегорія: {$task->category}.\r\nОпис: {$task->description}.",
                ]);

                app('Telegram')->getClient()->sendMessage([
                    'chat_id' => $task->getChatIdByCategory(),
                    'text' => "Нову завдання.\r\nКатегорія: {$task->category}.\r\nОпис: {$task->description}.",
                ]);

                $pendingChat->delete();
                continue;
            }

            if ($chat['type'] === 'private' && $message['text'] === '+') {
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
                $filteredUpdates[] = $update;
            }
        }

        return $filteredUpdates;
    }

}
