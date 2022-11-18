<?php

namespace App\Providers;

use App\Clients\TelegramClient;
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
    }
}
