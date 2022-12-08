<?php

namespace App\Console\Commands;

use App\Clients\TelegramClient;
use App\Helpers\Telegram;
use App\Models\LastOffset;
use App\Models\PendingRegisterChat;
use App\Models\RegisterRequest;
use App\Models\RespondedRegisterMessage;
use App\Models\Task;
use Illuminate\Console\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Exceptions\TelegramSDKException;

class Register extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register';

    /**
     * @var TelegramClient
     */
    protected $client;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Register');
        $this->client = app('Telegram-Register');

        $offset = LastOffset::first()->register_offset;
        $updates = $this->client->getUpdates($offset, true);

        if (count($updates['result'])  > 0) {
            $newOffset = $updates['result'][count($updates['result']) - 1]['update_id'];
            $newOffset -= 50;

            if ($newOffset <= 0) {
                $newOffset = 1;
            }

            LastOffset::first()->update([
                'register_offset' => $newOffset
            ]);
        }

        $lastMessages = $this->getLastMessage($updates);

        foreach ($lastMessages as $chatId => $messages) {
            $messagesCollection = collect($messages);
            $message = $messagesCollection->last();

            if (!$message)
                continue;

            $lastText = $messagesCollection->pluck('text')->last();

            if ($lastText === '+') {
                $registered = RegisterRequest::where('chat_id', $message['chat']['id'])
                    ->exists();

                if ($registered) {
                    $this->message(
                        $message['chat'],
                        'Здається ви вже зареєстровані. Якщо це помилка, або Вам потрібно оновити дані - зверніться до адміністратора.'
                    );

                    continue;
                }

                $this->sendKeyboard($message['chat']);

                PendingRegisterChat::create([
                    'chat_id' => $message['chat']['id'],
                ]);
            } else {
                $pendingChat = PendingRegisterChat::where('chat_id', $message['chat']['id'])
                    ->first();

                if (!$pendingChat) {
                    continue;
                }

                $registerRequest = RegisterRequest::where('chat_id', $message['chat']['id'])
                    ->first();

                if ($registerRequest) {
                    $category = $lastText;
                    $categoryValid = $this->validateCategory($category);

                    if ($categoryValid && empty($registerRequest->category)) {
                        $registerRequest->category = $message['text'];
                        $registerRequest->save();

                        $pendingChat->delete();

                        $this->registered($message['chat'], $registerRequest);
                    } else if (!empty($registerRequest->category)) {
                        $this->message(
                            $message['chat'],
                            'Здається для вашої реєстрації вже існує категорія. Зверніться до адміністратора.',
                        );

                        $pendingChat->delete();
                    } else {
                        $this->message(
                            $message['chat'],
                            'Здається ви вказали не правильну категорію. Спробуйте ще раз.',
                        );

                        $pendingChat->delete();
                    }
                } else {
                    $countOfLastMessages = $messagesCollection->count();

                    if ($countOfLastMessages >= 2) {
                        $category = $messagesCollection->pluck('text')->last();
                        $name = $messagesCollection->pluck('text')[$countOfLastMessages - 2];

                        if (!$this->validateName($name, $message['chat'], $pendingChat)) {
                            continue;
                        }

                        if (!$this->validateCategory($category)) {
                            $this->message(
                                $message['chat'],
                                'Здається ви ввели не коректну категорію. Спробуйте ще раз.'
                            );

                            $pendingChat->delete();

                            continue;
                        }

                        $registerRequest = RegisterRequest::create([
                            'chat_id' => $message['chat']['id'],
                            'telegram_first_name' => $message['chat']['first_name'],
                            'telegram_last_name' => $message['chat']['last_name'],
                            'name_pib' => $this->formatName($name),
                            'category' => $category,
                        ]);

                        $pendingChat->delete();

                        $this->registered($message['chat'], $registerRequest);
                    } else {
                        $name = $messagesCollection->pluck('text')->last();

                        if (!$this->validateName($name, $message['chat'], $pendingChat)) {
                            continue;
                        }

                        RegisterRequest::create([
                            'chat_id' => $message['chat']['id'],
                            'telegram_first_name' => $message['chat']['first_name'],
                            'telegram_last_name' => $message['chat']['last_name'],
                            'name_pib' => $this->formatName($name),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param array $chat
     * @return void
     */
    private function sendKeyboard(array $chat): void
    {
        $keyboard = [
            [Task::CATEGORY_ELECTRIC],
            [Task::CATEGORY_MECHANIC],
            [Task::CATEGORY_SANTEHNIK],
            [Task::CATEGORY_CLIMATE],
            [Task::CATEGORY_VENTILATSIYA],
            [Task::CATEGORY_GAS],
            [Task::CATEGORY_GENERAL],
        ];

        $replyMarkup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->client->getClient()->sendMessage([
            'chat_id' => $chat['id'],
            'text' => "Отже, ви хочете зареєструватись?\r\nВкажіть своє ім'я, прізвище, по-батькові та виберіть вашу галузь роботи.",
            'reply_markup' => $replyMarkup,
        ]);

    }

    /**
     * @param array $chat
     * @param string $message
     * @return void
     */
    private function message(array $chat, string $message): void
    {
        $this->client->getClient()->sendMessage([
            'chat_id' => $chat['id'],
            'text' => $message,
        ]);
    }

    /**
     * @param array $updates
     * @return array|null
     */
    private function getLastMessage(array $updates): ?array
    {
        $lastMessages = [];

        foreach ($updates['result'] as $update) {
            if (!isset($update['message'])) {
                continue;
            }

            $message = $update['message'];

            if (!isset($message['chat']))
                continue;

            $chat = $message['chat'];

            if ($chat['type'] !== 'private')
                continue;

            $lastMessages[$chat['id']][] = $message;
        }

        foreach ($lastMessages as $chatId => $messages) {
            foreach ($messages as $key => $message) {
                $responded = RespondedRegisterMessage::where('chat_id', $chatId)
                    ->where('message_id', $message['message_id'])
                    ->exists();

                if ($responded) {
                    unset($lastMessages[$chatId][$key]);
                } else {
                    RespondedRegisterMessage::create([
                        'chat_id' => $chatId,
                        'message_id' => $message['message_id'],
                    ]);
                }
            }
        }

        return $lastMessages;
    }

    /**
     * @param string $name
     * @param array $chat
     * @param PendingRegisterChat $pendingRegisterChat
     * @return bool
     */
    private function validateName(string $name, array $chat, PendingRegisterChat $pendingRegisterChat): bool
    {
        $nameParts = explode(' ', $name);
        $nameParts = array_filter($nameParts, function ($namePart) {
            return !empty($namePart) && strlen($namePart) > 3;
        });

        if (count($nameParts) !== 3) {
            $this->message(
                $chat,
                'Здається ви ввели не коректний ПІБ. Спробуйте ще раз.'
            );

            $pendingRegisterChat->delete();

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $category
     * @return bool
     */
    private function validateCategory(string $category): bool
    {
        return in_array(
            $category,
            [
                Task::CATEGORY_ELECTRIC,
                Task::CATEGORY_MECHANIC,
                Task::CATEGORY_SANTEHNIK,
                Task::CATEGORY_CLIMATE,
                Task::CATEGORY_VENTILATSIYA,
                Task::CATEGORY_GAS,
                Task::CATEGORY_GENERAL,
            ]
        );
    }

    /**
     * @param array $chat
     * @param RegisterRequest $registerRequest
     * @return void
     * @throws TelegramSDKException
     */
    private function registered(array $chat, RegisterRequest $registerRequest)
    {
        $this->message(
            $chat,
            'Ви зареєстровані. Вам буде відправлено запрошення у відповідні чат(и).',
        );

        $category = $registerRequest->category;

        if ($category !== Task::CATEGORY_GENERAL) {
            $chatId = Telegram::getChatIdByCategory($category);

            if (!$chatId) {
                $this->message(
                    $chat,
                    'Здається ви вказали категорію яка з певних причин не підтримується. Зверніться до адміністратора.',
                );

                return;
            }

            $response = $this->client->getClient()->createChatInviteLink([
                'chat_id' => $chatId,
                'name' => 'Ссилка.',
                'expire_date' => now()->addHour(),
                'member_limit' => 1,
                'creates_join_request' => false,
            ]);

            $this->message(
                $chat,
                'Перейдіть по ссилці, щоб приєднатись в чат: ' . $response->invite_link,
            );
        } else {
            $this->message(
                $chat,
                'Оскільки ви обрали загальну категорію, вам буде відправлено ссилки на всі чати.',
            );

            foreach (Task::categories() as $category) {
                $chatId = Telegram::getChatIdByCategory($category);

                $response = $this->client->getClient()->createChatInviteLink([
                    'chat_id' => $chatId,
                    'name' => 'Ссилка.',
                    'expire_date' => now()->addHour(),
                    'member_limit' => 1,
                    'creates_join_request' => false,
                ]);

                $this->message(
                    $chat,
                    $response->invite_link,
                );
            }
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function formatName(string $name): string
    {
        $name = trim($name);
        $nameParts = explode(' ', $name);
        $nameParts = array_filter($nameParts, function ($namePart) {
            return !empty($namePart);
        });

        return implode(' ', $nameParts);
    }
}
