<?php

namespace App\Clients;

use GuzzleHttp\Client;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use GuzzleHttp\Exception\GuzzleException;

class TelegramClient
{
    protected Api $telegramSDK;

    /**
     * @throws TelegramSDKException
     */
    public function __construct()
    {
        $this->telegramSDK = new Api(config('telegram.api-key'));
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
        $token = config('telegram.api-key');

        $res = $client
            ->get("https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}")
            ->getBody()
            ->getContents();

        return json_decode($res, true);
    }
}
