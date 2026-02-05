<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Contact;

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

        $sources = config('reos.leads.sources', [
            'website' => 'Website',
            'referral' => 'Referans',
            'portal' => 'Portal',
            'social' => 'Sosyal Medya',
            'walk_in' => 'Ofis Ziyareti',
            'phone' => 'Telefon',
            'other' => 'Diğer',
        ]);

        return view('crm::leads.create', compact('agents', 'sources'));
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
            'source_type' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_locations' => 'nullable|array',
            'property_type' => 'nullable|string',
            'listing_type' => 'nullable|in:sale,rent',
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

        // Create lead
        $lead = Lead::create([
            'contact_id' => $contact->id,
            'source_type' => $validated['source_type'],
            'assigned_to' => $validated['assigned_to'],
            'status' => 'new',
            'score' => 50,
            'notes' => $validated['notes'] ?? null,
            'requirements' => [
                'budget_min' => $validated['budget_min'] ?? null,
                'budget_max' => $validated['budget_max'] ?? null,
                'preferred_locations' => $validated['preferred_locations'] ?? [],
                'property_type' => $validated['property_type'] ?? null,
                'listing_type' => $validated['listing_type'] ?? null,
            ],
        ]);

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Lead başarıyla oluşturuldu.');
    }

    /**
     * Display the specified lead
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'contact',
            'assignedTo',
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

        return view('crm::leads.edit', compact('lead', 'agents', 'sources'));
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,converted,lost',
            'assigned_to' => 'required|exists:users,id',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_locations' => 'nullable|array',
            'property_type' => 'nullable|string',
            'listing_type' => 'nullable|in:sale,rent',
        ]);

        $lead->update([
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to'],
            'score' => $validated['score'] ?? $lead->score,
            'notes' => $validated['notes'],
            'requirements' => [
                'budget_min' => $validated['budget_min'] ?? null,
                'budget_max' => $validated['budget_max'] ?? null,
                'preferred_locations' => $validated['preferred_locations'] ?? [],
                'property_type' => $validated['property_type'] ?? null,
                'listing_type' => $validated['listing_type'] ?? null,
            ],
        ]);

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Lead başarıyla güncellendi.');
    }

    /**
     * Remove the specified lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead başarıyla silindi.');
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
            ->with('success', 'Lead başarıyla fırsata dönüştürüldü.');
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

        return back()->with('success', 'Lead kaybedildi olarak işaretlendi.');
    }
}
