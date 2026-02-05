<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Telegram
{
    public static function send(string $message): void
    {
        try {
            Http::timeout(5)->post(
                "https://api.telegram.org/bot" . config('app.telegram_token') . "/sendMessage",
                [
                    'chat_id' => config('app.telegram_chat_id'),
                    'text'    => $message,
                    'parse_mode' => 'HTML'
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Telegram notification failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
