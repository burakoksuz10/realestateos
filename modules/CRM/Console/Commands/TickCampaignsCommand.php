<?php

namespace Modules\CRM\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\Services\DripExecutor;

class TickCampaignsCommand extends Command
{
    protected $signature = 'campaigns:tick {--limit=50 : Max enrollments per tick}';

    protected $description = 'Vadesi dolan drip kampanya enrollment\'larını çalıştır.';

    public function handle(DripExecutor $executor): int
    {
        $limit = (int) $this->option('limit');
        $this->info("Drip tick: en çok {$limit} enrollment");

        $results = $executor->tick($limit);

        $this->table(
            ['ran', 'completed', 'failed', 'skipped'],
            [[
                $results['ran']       ?? 0,
                $results['completed'] ?? 0,
                $results['failed']    ?? 0,
                $results['skipped']   ?? 0,
            ]],
        );

        return self::SUCCESS;
    }
}
