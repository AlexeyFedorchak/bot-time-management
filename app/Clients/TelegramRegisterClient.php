<?php

namespace App\Clients;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramRegisterClient extends TelegramClient
{
    /**
     * @throws TelegramSDKException
     */
    public function __construct()
    {
        parent::__construct();

        $this->apiKey = config('telegram.api-register-key');
        $this->telegramSDK = new Api($this->apiKey);
    }
}
