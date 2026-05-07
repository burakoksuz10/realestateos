<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;

class UserController extends Controller
{
    public function index()
    {
        return $this->paginated(User::with(['office', 'team'])->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:8',
            'phone'     => 'nullable|string|max:20',
            'office_id' => 'nullable|exists:offices,id',
            'team_id'   => 'nullable|exists:teams,id',
        ]);

        $data['password'] = Hash::make($data['password']);

        return $this->success(User::create($data), 'Kullanıcı oluşturuldu.', 201);
    }

    public function show(User $user)
    {
        return $this->success($user->load(['office', 'team', 'roles']));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'phone'     => 'nullable|string|max:20',
            'office_id' => 'nullable|exists:offices,id',
            'team_id'   => 'nullable|exists:teams,id',
        ]);

        $user->update($data);

        return $this->success($user, 'Kullanıcı güncellendi.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return $this->success(null, 'Kullanıcı silindi.');
    }

    public function performance(User $user)
    {
        return $this->success([
            'total_leads'  => Lead::where('assigned_to', $user->id)->count(),
            'active_leads' => Lead::where('assigned_to', $user->id)->where('status', 'new')->count(),
            'total_deals'  => Deal::where('assigned_to', $user->id)->count(),
            'won_deals'    => Deal::where('assigned_to', $user->id)->where('status', 'won')->count(),
            'revenue'      => Deal::where('assigned_to', $user->id)->where('status', 'won')->sum('value'),
        ]);
    }
}
