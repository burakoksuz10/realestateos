<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\Deal;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Services\PipelineAutoActionExecutor;

/**
 * Pipeline stage auto-action tetikleyici.
 *
 * `updated` event'inde stage_id değiştiyse yeni stage'in
 * auto_actions JSON'unu çalıştırır. Telegram bildirim observer'ından
 * AYRI bir observer — concern ayrı, sıra önemli değil.
 */
class DealStageObserver
{
    public function __construct(protected PipelineAutoActionExecutor $executor) {}

    public function updated(Deal $deal): void
    {
        if (!$deal->wasChanged('stage_id')) {
            return;
        }
        if (!$deal->stage_id) {
            return;
        }

        $stage = PipelineStage::find($deal->stage_id);
        if (!$stage) return;

        $this->executor->onStageEntered($deal, $stage);
    }

    public function created(Deal $deal): void
    {
        // Yeni oluşan deal'lar için de aksiyonlar çalışsın
        if (!$deal->stage_id) return;
        $stage = PipelineStage::find($deal->stage_id);
        if (!$stage) return;

        $this->executor->onStageEntered($deal, $stage);
    }
}
