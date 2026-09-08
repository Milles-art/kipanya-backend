<?php

namespace App\Console\Commands;

use App\Enums\Content\ContentStatus;
use App\Models\Content\Cartoon;
use App\Models\Content\CartoonEpisode;
use Illuminate\Console\Command;

class PublishScheduledCartoons extends Command
{
    protected $signature = 'kipanya:publish-scheduled-cartoons';
    protected $description = 'Publish cartoons whose scheduled publication time has arrived.';

    public function handle(): int
    {
        $cartoons = Cartoon::query()
            ->where('status', ContentStatus::Scheduled->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => ContentStatus::Published->value]);

        $episodes = CartoonEpisode::query()
            ->where('status', ContentStatus::Scheduled->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => ContentStatus::Published->value]);

        $this->info("Published {$cartoons} scheduled cartoon(s) and {$episodes} scheduled episode(s).");
        return self::SUCCESS;
    }
}
