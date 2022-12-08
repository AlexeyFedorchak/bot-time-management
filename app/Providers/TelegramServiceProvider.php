<?php

namespace App\Providers;

use App\Clients\TelegramClient;
use App\Clients\TelegramRegisterClient;
use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        app()->bind(
            'Telegram',
            TelegramClient::class
        );

        app()->bind(
            'Telegram-Register',
            TelegramRegisterClient::class
        );
    }
}
