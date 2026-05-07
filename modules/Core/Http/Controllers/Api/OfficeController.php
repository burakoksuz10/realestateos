<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Office;

class OfficeController extends Controller
{
    public function index()
    {
        return $this->paginated(Office::withCount('users')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'city'    => 'required|string',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email',
        ]);

        return $this->success(Office::create($data), 'Ofis oluşturuldu.', 201);
    }

    public function show(Office $office)
    {
        return $this->success($office->load('users'));
    }

    public function update(Request $request, Office $office)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'city'    => 'required|string',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email',
        ]);

        $office->update($data);

        return $this->success($office, 'Ofis güncellendi.');
    }

    public function destroy(Office $office)
    {
        $office->delete();

        return $this->success(null, 'Ofis silindi.');
    }

    public function users(Office $office)
    {
        return $this->success($office->users()->paginate(20));
    }

    public function stats(Office $office)
    {
        return $this->success([
            'agents'          => $office->users()->count(),
            'active_listings' => $office->listings()->where('status', 'active')->count(),
            'total_listings'  => $office->listings()->count(),
        ]);
    }
}
