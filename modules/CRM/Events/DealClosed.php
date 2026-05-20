<?php

namespace Modules\CRM\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\Models\Deal;

class DealClosed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Deal $deal,
        public string $outcome, // 'won' | 'lost'
    ) {
    }
}
