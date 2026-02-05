<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Office;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::withCount('users')->paginate(20);
        return view('core::offices.index', compact('offices'));
    }

    public function create()
    {
        return view('core::offices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:offices,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $office = Office::create($validated);

        return redirect()->route('admin.offices.show', $office)
            ->with('success', 'Ofis başarıyla oluşturuldu.');
    }

    public function show(Office $office)
    {
        $office->load(['users', 'manager']);
        return view('core::offices.show', compact('office'));
    }

    public function edit(Office $office)
    {
        return view('core::offices.edit', compact('office'));
    }

    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:offices,code,' . $office->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $office->update($validated);

        return redirect()->route('admin.offices.show', $office)
            ->with('success', 'Ofis başarıyla güncellendi.');
    }

    public function destroy(Office $office)
    {
        if ($office->users()->exists()) {
            return back()->with('error', 'Bu ofiste kullanıcılar var, silinemez.');
        }

        $office->delete();

        return redirect()->route('admin.offices.index')
            ->with('success', 'Ofis başarıyla silindi.');
    }
}
