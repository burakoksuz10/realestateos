<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Contact;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('crm::contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('crm::contacts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'type' => 'required|in:individual,company',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);

        return redirect()->route('admin.contacts.show', $contact)
            ->with('success', 'Kişi başarıyla oluşturuldu.');
    }

    public function show(Contact $contact)
    {
        return view('crm::contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        return view('crm::contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'type' => 'required|in:individual,company',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        return redirect()->route('admin.contacts.show', $contact)
            ->with('success', 'Kişi başarıyla güncellendi.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')
            ->with('success', 'Kişi başarıyla silindi.');
    }

    public function toggleStatus(Contact $contact)
    {
        $contact->update(['is_active' => !$contact->is_active]);
        return back()->with('success', 'Durum güncellendi.');
    }

    public function activities(Contact $contact)
    {
        $activities = $contact->activities()->latest()->paginate(20);
        return view('crm::contacts.activities', compact('contact', 'activities'));
    }

    public function import(Request $request)
    {
        return back()->with('info', 'İçe aktarma özelliği yakında eklenecek.');
    }

    public function export()
    {
        return back()->with('info', 'Dışa aktarma özelliği yakında eklenecek.');
    }
}
