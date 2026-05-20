<?php

namespace Modules\CRM\Http\Controllers\Concerns;

/**
 * Office isolation guard for IDOR koruması.
 *
 * Multi-office SaaS'ta bir kullanıcı route model binding ile
 * (`Lead $lead`) farklı bir ofisin kaydına doğrudan erişebilir.
 * Bu trait her show/edit/update/destroy başında çağrılmalı.
 *
 * Kullanım:
 *   class LeadController {
 *       use EnforcesOfficeIsolation;
 *       public function show(Lead $lead) {
 *           $this->ensureSameOffice($lead);
 *           ...
 *       }
 *   }
 */
trait EnforcesOfficeIsolation
{
    /**
     * Kullanıcının ofisi modelin ofisi ile aynı değilse 403 fırlat.
     * office_id'si olmayan modeller (örn. global ayarlar) için geçilir.
     */
    protected function ensureSameOffice($model): void
    {
        $userOfficeId = auth()->user()?->office_id;

        // Superadmin (office_id NULL) veya tek-ofis modunda kontrol atla
        if (!$userOfficeId) return;

        // Model'in office_id'si yoksa skip
        if (!isset($model->office_id)) return;

        if ((int) $model->office_id !== (int) $userOfficeId) {
            abort(403, 'Bu kayda erişim yetkiniz yok.');
        }
    }
}
