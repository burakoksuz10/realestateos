<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\AI\Services\NewsService;

class FetchNewsCommand extends Command
{
    protected $signature = 'news:fetch';
    protected $description = 'Fetch latest real estate news from RSS feeds and enrich with AI';

    public function handle(NewsService $newsService): int
    {
        $this->info('Haberler çekiliyor...');

        $result = $newsService->fetch();

        $this->info("✓ {$result['new']} yeni haber eklendi.");

        return Command::SUCCESS;
    }
}
