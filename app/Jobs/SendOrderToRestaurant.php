<?php

namespace App\Jobs;

namespace App\Jobs;

use App\Helpers\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendOrderToRestaurant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 15;
    public int $tries = 3;

    protected array $orderData;
    protected int $restaurantId;
    protected string $website;

    public function __construct(array $orderData, int $restaurantId, string $website)
    {
        $this->orderData    = $orderData;
        $this->restaurantId = $restaurantId;
        $this->website      = rtrim($website, '/');
    }

    public function handle(): void
    {
        $pid = $this->orderData['pid'] ?? null;

        if (!$pid) {
            Telegram::send("❌ <b>Order Job</b>\nPID missing\n" . json_encode($this->orderData));
            return;
        }

        if (DB::table('orders')->where('pid', $pid)->exists()) {
            return;
        }

        $providerId = DB::table('providers')
            ->where('provider_id', $this->orderData['providerId'] ?? null)
            ->value('id');

        DB::table('orders')->insert([
            'id'            => Str::uuid(),
            'order_id'      => $this->orderData['_id'] ?? $this->orderData['id'] ?? $pid,
            'pid'           => $pid,
            'restaurant_id' => $this->restaurantId,
            'provider_id'   => $providerId,
            'shortCode'     => $this->orderData['shortCode'] ?? null,
            'status'        => $this->orderData['status'] ?? null,
            'data'          => json_encode($this->orderData, JSON_UNESCAPED_UNICODE),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        try {
            Http::timeout(5)
                ->retry(2, 200)
                ->post(
                    "{$this->website}/entegra/add-order",
                    $this->orderData
                );
        } catch (\Throwable $e) {
            Log::error("pid {$pid} | ". $e->getMessage());
            Telegram::send(
                "🚨 <b>Order Send Failed</b>\n"
                . "<b>PID:</b> {$pid}\n"
                . "<b>Restaurant ID:</b> {$this->restaurantId}\n"
                . "<b>URL:</b> {$this->website}\n"
                . "<b>Error:</b> {$e->getMessage()}"
            );

            throw $e; // retry çalışsın
        }
    }

    // ❗ Job tamamen fail olursa
    public function failed(\Throwable $e): void
    {
        Telegram::send(
            "🔥 <b>Order Job Failed Completely</b>\n"
            . "<b>PID:</b> " . ($this->orderData['pid'] ?? 'N/A') . "\n"
            . "<b>Error:</b> {$e->getMessage()}"
        );
    }
}
