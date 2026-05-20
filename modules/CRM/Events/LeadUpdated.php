<?php

namespace Modules\CRM\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\Models\Lead;

class LeadUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Lead $lead)
    {
    }
}
