<?php

namespace App\Clients;

use App\Models\Task;
use GuzzleHttp\Client;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use GuzzleHttp\Exception\GuzzleException;

class TelegramClient
{
    protected Api $telegramSDK;

    protected string $apiKey;

    /**
     * @throws TelegramSDKException
     */
    public function __construct()
    {
        $this->apiKey = config('telegram.api-key');
        $this->telegramSDK = new Api($this->apiKey);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->telegramSDK->$name($arguments);
    }

    /**
     * @return Api
     */
    public function getClient(): Api
    {
        return $this->telegramSDK;
    }

    /**
     * @param $offset
     * @return array
     * @throws GuzzleException
     */
    public function getUpdates($offset): array
    {
        $client = new Client();
        $token = $this->apiKey;

        $res = $client
            ->get("https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}")
            ->getBody()
            ->getContents();

        return json_decode($res, true);
    }

    /**
     * @param Task $task
     * @return string
     * @throws GuzzleException
     */
    public function getFileUrl(Task $task): string
    {
        $client = new Client();
        $token = $this->apiKey;

        $photos = json_decode($task->photo);
        $response = $client
            ->get("https://api.telegram.org/bot$token/getFile?file_id={$photos[2]->file_id}")
            ->getBody()
            ->getContents();

        $response = json_decode($response);

        return "https://api.telegram.org/file/bot$token/{$response->result->file_path}";
    }

}
