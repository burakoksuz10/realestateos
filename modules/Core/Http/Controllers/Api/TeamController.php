<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Team;

class TeamController extends Controller
{
    public function index()
    {
        return $this->paginated(Team::withCount('users')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id'   => 'nullable|exists:users,id',
            'office_id'   => 'nullable|exists:offices,id',
        ]);

        return $this->success(Team::create($data), 'Takım oluşturuldu.', 201);
    }

    public function show(Team $team)
    {
        return $this->success($team->load(['users', 'leader']));
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id'   => 'nullable|exists:users,id',
            'office_id'   => 'nullable|exists:offices,id',
        ]);

        $team->update($data);

        return $this->success($team, 'Takım güncellendi.');
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return $this->success(null, 'Takım silindi.');
    }

    public function members(Team $team)
    {
        return $this->success($team->users()->paginate(20));
    }
}
