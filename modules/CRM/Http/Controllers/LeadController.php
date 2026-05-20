<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Pipeline;

class LeadController extends Controller
{
    /**
     * Display a listing of leads
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Lead::with(['contact', 'assignedTo', 'source']);

        // Filter by user if not admin
        if (!$user->isAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source_type', $request->source);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('score_min')) {
            $query->where('score', '>=', $request->score_min);
        }

        if ($request->filled('search')) {
            $query->whereHas('contact', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $leads = $query->paginate(20);

        // Get agents for filter
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        return view('crm::leads.index', compact('leads', 'agents'));
    }

    /**
     * Show the form for creating a new lead
     */
    public function create()
    {
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        $listings = \Modules\RealEstate\Models\Listing::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'reference_no', 'type', 'price']);

        $sources = config('reos.leads.sources', [
            'website' => 'Website',
            'referral' => 'Referans',
            'portal' => 'Portal',
            'social' => 'Sosyal Medya',
            'walk_in' => 'Ofis Ziyareti',
            'phone' => 'Telefon',
            'other' => 'Diğer',
        ]);

        return view('crm::leads.create', compact('agents', 'sources', 'listings'));
    }

    /**
     * Store a newly created lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string',
            'interest_type' => 'nullable|string|in:buy,rent,sell,invest',
            'property_type' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:new,contacted,qualified,proposal',
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_locations' => 'nullable|string',
            'interested_listings' => 'nullable|array',
            'interested_listings.*' => 'exists:listings,id',
        ]);

        // Create or find contact
        $contact = Contact::firstOrCreate(
            ['email' => $validated['email'] ?? null, 'phone' => $validated['phone'] ?? null],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'type' => 'individual',
            ]
        );

        // Parse preferred locations from comma-separated text
        $preferredLocations = [];
        if (!empty($validated['preferred_locations'])) {
            $preferredLocations = array_values(array_filter(array_map('trim', explode(',', $validated['preferred_locations']))));
        }

        // Create lead
        $lead = Lead::create([
            'contact_id' => $contact->id,
            'source' => $validated['source'] ?? null,
            'interest_type' => $validated['interest_type'] ?? null,
            'property_type' => $validated['property_type'] ?? null,
            'assigned_to' => $validated['assigned_to'],
            'status' => $validated['status'] ?? 'new',
            'priority' => $validated['priority'] ?? 'medium',
            'score' => 50,
            'notes' => $validated['notes'] ?? null,
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'preferred_locations' => $preferredLocations,
        ]);

        $lead->interestedListings()->sync($request->input('interested_listings', []));

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Potansiyel müşteri başarıyla oluşturuldu.');
    }

    /**
     * Display the specified lead
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'contact',
            'assignedTo',
            'interestedListings',
            'activities' => fn($q) => $q->latest()->take(10),
            'tasks' => fn($q) => $q->where('status', '!=', 'completed')->orderBy('due_date'),
            'deals',
        ]);

        return view('crm::leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead
     */
    public function edit(Lead $lead)
    {
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        $sources = config('reos.leads.sources', [
            'website' => 'Website',
            'referral' => 'Referans',
            'portal' => 'Portal',
            'social' => 'Sosyal Medya',
            'walk_in' => 'Ofis Ziyareti',
            'phone' => 'Telefon',
            'other' => 'Diğer',
        ]);

        $listings = \Modules\RealEstate\Models\Listing::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'reference_no', 'type', 'price']);

        $lead->load('interestedListings');

        return view('crm::leads.edit', compact('lead', 'agents', 'sources', 'listings'));
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,converted,lost',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'required|exists:users,id',
            'source' => 'nullable|string',
            'interest_type' => 'nullable|string|in:buy,rent,sell,invest',
            'property_type' => 'nullable|string',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_locations' => 'nullable|string',
            'interested_listings' => 'nullable|array',
            'interested_listings.*' => 'exists:listings,id',
        ]);

        // Parse preferred locations from comma-separated text
        $preferredLocations = [];
        if (!empty($validated['preferred_locations'])) {
            $preferredLocations = array_values(array_filter(array_map('trim', explode(',', $validated['preferred_locations']))));
        }

        $lead->update([
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? $lead->priority,
            'assigned_to' => $validated['assigned_to'],
            'source' => $validated['source'] ?? $lead->source,
            'interest_type' => $validated['interest_type'] ?? $lead->interest_type,
            'property_type' => $validated['property_type'] ?? $lead->property_type,
            'score' => $validated['score'] ?? $lead->score,
            'notes' => $validated['notes'],
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'preferred_locations' => $preferredLocations,
        ]);

        $lead->interestedListings()->sync($request->input('interested_listings', []));

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Potansiyel müşteri başarıyla güncellendi.');
    }

    /**
     * Remove the specified lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', 'Potansiyel müşteri başarıyla silindi.');
    }

    /**
     * Convert lead to deal
     */
    public function convert(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'pipeline_id' => 'required|exists:pipelines,id',
            'value' => 'nullable|numeric|min:0',
            'listing_id' => 'nullable|exists:listings,id',
        ]);

        $deal = $lead->convertToDeal($validated);

        return redirect()->route('admin.deals.show', $deal)
            ->with('success', 'Potansiyel müşteri başarıyla fırsata dönüştürüldü.');
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'lost_reason' => 'required|string|max:255',
        ]);

        $lead->update([
            'status' => 'lost',
            'lost_reason' => $validated['lost_reason'],
            'lost_at' => now(),
        ]);

        return back()->with('success', 'Potansiyel müşteri kaybedildi olarak işaretlendi.');
    }

    public function kanban(Request $request)
    {
        $user = $request->user();

        $pipeline = Pipeline::with(['stages.leads' => function ($query) use ($user) {
            if (!$user->isAdmin()) {
                $query->where('assigned_to', $user->id);
            }
            $query->with(['contact', 'assignedTo']);
        }])->where('type', 'lead')->where('is_default', true)->first();

        if (!$pipeline) {
            $pipeline = Pipeline::with(['stages.leads'])->where('type', 'lead')->first();
        }

        $pipelines = Pipeline::where('type', 'lead')->get();

        return view('crm::leads.kanban', compact('pipeline', 'pipelines'));
    }

    public function assign(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $lead->update(['assigned_to' => $validated['assigned_to']]);

        $user = \App\Models\User::find($validated['assigned_to']);

        return response()->json([
            'success' => true,
            'message' => 'Potansiyel müşteri başarıyla atandı.',
            'assigned_to' => ['id' => $user->id, 'name' => $user->name],
        ]);
    }

    public function moveStage(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $lead->update(['stage_id' => $validated['stage_id']]);

        return response()->json(['success' => true, 'lead' => $lead->fresh()]);
    }

    public function qualify(Request $request, Lead $lead)
    {
        $request->validate([
            'qualification_notes' => 'nullable|string|max:1000',
        ]);

        $lead->update([
            'is_qualified' => true,
            'status' => 'qualified',
            'qualification_notes' => $request->qualification_notes,
        ]);

        return back()->with('success', 'Potansiyel müşteri nitelikli olarak işaretlendi.');
    }

    public function suggestions(Lead $lead)
    {
        $suggestions = $lead->ai_suggestions ?? [
            'Müşteri ile takip görüşmesi planlayın.',
            'Bütçeye uygun ilanları paylaşın.',
            'Tercih edilen bölgelerde yeni ilanları gönderin.',
        ];

        return response()->json(['success' => true, 'suggestions' => $suggestions]);
    }

    public function reanalyze(Lead $lead)
    {
        if (!config('reos.ai.enabled', true)) {
            return response()->json([
                'queued' => false,
                'message' => 'AI özellikleri devre dışı. Ayarlar > AI üzerinden açın.',
            ], 422);
        }

        \App\Jobs\AI\AnalyzeLeadJob::dispatch($lead->id, auth()->id());

        return response()->json([
            'queued' => true,
            'message' => 'AI analizi başlatıldı. Birkaç saniye içinde sayfa yenilendiğinde görünecek.',
        ]);
    }
}
