<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\CampaignEnrollment;
use Modules\CRM\Models\Lead;
use Modules\CRM\Services\DripExecutor;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::query()
            ->withCount(['steps', 'enrollments'])
            ->orderByDesc('is_active')
            ->orderBy('name');

        if ($officeId = $request->user()->office_id ?? null) {
            $query->where(function ($q) use ($officeId) {
                $q->whereNull('office_id')->orWhere('office_id', $officeId);
            });
        }

        $campaigns = $query->get();

        $stats = [
            'total'   => $campaigns->count(),
            'active'  => $campaigns->where('is_active', true)->count(),
            'active_enrollments' => CampaignEnrollment::active()->count(),
        ];

        return view('crm::campaigns.index', compact('campaigns', 'stats'));
    }

    public function show(Request $request, Campaign $campaign)
    {
        $this->authorizeView($request, $campaign);

        $campaign->load(['steps']);

        $enrollments = CampaignEnrollment::where('campaign_id', $campaign->id)
            ->with(['lead.contact', 'currentStep'])
            ->orderByDesc('enrolled_at')
            ->limit(50)
            ->get();

        $stats = [
            'active'    => $campaign->enrollments()->where('status', 'active')->count(),
            'completed' => $campaign->enrollments()->where('status', 'completed')->count(),
            'failed'    => $campaign->enrollments()->where('status', 'failed')->count(),
            'paused'    => $campaign->enrollments()->where('status', 'paused')->count(),
        ];

        return view('crm::campaigns.show', compact('campaign', 'enrollments', 'stats'));
    }

    public function toggleActive(Request $request, Campaign $campaign)
    {
        $this->authorizeView($request, $campaign);

        $campaign->update(['is_active' => !$campaign->is_active]);

        return back()->with('success', $campaign->is_active ? 'Kampanya aktifleştirildi' : 'Kampanya duraklatıldı');
    }

    public function enroll(Request $request, Campaign $campaign, DripExecutor $executor)
    {
        $this->authorizeView($request, $campaign);

        $data = $request->validate([
            'lead_id' => 'required|integer|exists:leads,id',
        ]);

        $lead = Lead::findOrFail($data['lead_id']);
        $enrollment = $executor->enroll($campaign, $lead, $request->user()->id);

        if (!$enrollment) {
            return back()->withErrors(['enroll' => 'Enroll edilemedi — kampanya aktif değil veya step yok.']);
        }

        return back()->with('success', "Lead #{$lead->id} kampanyaya eklendi.");
    }

    public function cancelEnrollment(Request $request, CampaignEnrollment $enrollment)
    {
        $campaign = $enrollment->campaign;
        $this->authorizeView($request, $campaign);

        $enrollment->update(['status' => 'cancelled', 'next_run_at' => null]);

        return back()->with('success', 'Enrollment iptal edildi.');
    }

    public function tick(Request $request, DripExecutor $executor)
    {
        $results = $executor->tick((int) $request->input('limit', 50));

        return back()->with('success', sprintf(
            'Tick: %d çalıştı, %d tamamlandı, %d başarısız.',
            $results['ran'] ?? 0,
            $results['completed'] ?? 0,
            $results['failed'] ?? 0,
        ));
    }

    protected function authorizeView(Request $request, Campaign $campaign): void
    {
        $officeId = $request->user()->office_id ?? null;
        if ($officeId && $campaign->office_id && $campaign->office_id !== $officeId) {
            abort(403);
        }
    }
}
