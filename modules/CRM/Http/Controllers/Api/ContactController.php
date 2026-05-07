<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Contact;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('assignedTo')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest();

        return $this->paginated($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'nullable|email|unique:contacts',
            'phone'       => 'nullable|string|max:20',
            'city'        => 'nullable|string',
            'source'      => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'notes'       => 'nullable|string',
        ]);

        return $this->success(Contact::create($data), 'Kişi oluşturuldu.', 201);
    }

    public function show(Contact $contact)
    {
        return $this->success($contact->load(['leads', 'deals', 'activities']));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'nullable|email|unique:contacts,email,' . $contact->id,
            'phone'       => 'nullable|string|max:20',
            'city'        => 'nullable|string',
            'source'      => 'nullable|string',
            'status'      => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'notes'       => 'nullable|string',
        ]);

        $contact->update($data);

        return $this->success($contact, 'Kişi güncellendi.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return $this->success(null, 'Kişi silindi.');
    }
}
