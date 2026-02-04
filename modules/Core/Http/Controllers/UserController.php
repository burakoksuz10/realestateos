<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['office', 'team', 'roles']);

        // Filter by office
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);
        $roles = Role::all();
        $offices = \Modules\Core\Models\Office::active()->get();

        return view('core::users.index', compact('users', 'roles', 'offices'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $offices = \Modules\Core\Models\Office::active()->get();
        $teams = \Modules\Core\Models\Team::active()->get();

        return view('core::users.create', compact('roles', 'offices', 'teams'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::defaults()],
            'title' => ['nullable', 'string', 'max:100'],
            'office_id' => ['nullable', 'exists:offices,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'title' => $validated['title'] ?? null,
            'office_id' => $validated['office_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['office', 'team', 'roles', 'leads', 'deals', 'listings']);
        
        // Get user statistics
        $stats = [
            'total_leads' => $user->leads()->count(),
            'converted_leads' => $user->leads()->where('status', 'converted')->count(),
            'total_deals' => $user->deals()->count(),
            'won_deals' => $user->deals()->where('status', 'won')->count(),
            'total_revenue' => $user->deals()->where('status', 'won')->sum('value'),
            'active_listings' => $user->listings()->where('status', 'active')->count(),
        ];

        // Get recent activities
        $activities = \Modules\CRM\Models\Activity::where('user_id', $user->id)
            ->with(['contact', 'lead', 'deal'])
            ->latest()
            ->take(10)
            ->get();

        return view('core::users.show', compact('user', 'stats', 'activities'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $offices = \Modules\Core\Models\Office::active()->get();
        $teams = \Modules\Core\Models\Team::active()->get();

        return view('core::users.edit', compact('user', 'roles', 'offices', 'teams'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'title' => ['nullable', 'string', 'max:100'],
            'office_id' => ['nullable', 'exists:offices,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'title' => $validated['title'] ?? null,
            'office_id' => $validated['office_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendinizi silemezsiniz.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla silindi.');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'Kullanıcı durumu güncellendi.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Şifre başarıyla sıfırlandı.');
    }

    /**
     * Get user activity log
     */
    public function activity(User $user)
    {
        $activities = \Modules\Core\Models\AuditLog::where('user_id', $user->id)
            ->latest()
            ->paginate(50);

        return view('core::users.activity', compact('user', 'activities'));
    }
}
