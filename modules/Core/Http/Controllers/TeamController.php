<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Team;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::withCount('members')->paginate(20);
        return view('core::teams.index', compact('teams'));
    }

    public function create()
    {
        return view('core::teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        $team = Team::create($validated);

        return redirect()->route('admin.teams.show', $team)
            ->with('success', 'Takım başarıyla oluşturuldu.');
    }

    public function show(Team $team)
    {
        $team->load(['members', 'leader']);
        return view('core::teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        return view('core::teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        $team->update($validated);

        return redirect()->route('admin.teams.show', $team)
            ->with('success', 'Takım başarıyla güncellendi.');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->route('admin.teams.index')
            ->with('success', 'Takım başarıyla silindi.');
    }

    public function addMember(Request $request, Team $team)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $team->members()->attach($validated['user_id']);

        return back()->with('success', 'Üye eklendi.');
    }

    public function removeMember(Team $team, $user)
    {
        $team->members()->detach($user);
        return back()->with('success', 'Üye çıkarıldı.');
    }
}
