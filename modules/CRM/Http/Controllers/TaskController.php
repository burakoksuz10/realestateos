<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Task;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Task::with(['assignedTo', 'contact', 'lead', 'deal']);

        if (!$user->isAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->orderBy('due_date', 'asc')->paginate(20);

        return view('crm::tasks.index', compact('tasks'));
    }

    public function calendar(Request $request)
    {
        $user = $request->user();
        
        $query = Task::with(['assignedTo', 'contact']);

        if (!$user->isAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        $tasks = $query->get();

        return view('crm::tasks.calendar', compact('tasks'));
    }

    public function create()
    {
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        return view('crm::tasks.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_date' => 'required|date',
            'assigned_to' => 'required|exists:users,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'lead_id' => 'nullable|exists:leads,id',
            'deal_id' => 'nullable|exists:deals,id',
        ]);

        $validated['status'] = 'pending';
        $validated['created_by'] = auth()->id();

        $task = Task::create($validated);

        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'Görev başarıyla oluşturuldu.');
    }

    public function show(Task $task)
    {
        $task->load(['assignedTo', 'contact', 'lead', 'deal', 'createdBy']);
        return view('crm::tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        return view('crm::tasks.edit', compact('task', 'agents'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_date' => 'required|date',
            'assigned_to' => 'required|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update($validated);

        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'Görev başarıyla güncellendi.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Görev başarıyla silindi.');
    }

    public function complete(Task $task)
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Görev tamamlandı.');
    }

    public function reschedule(Request $request, Task $task)
    {
        $validated = $request->validate([
            'due_date' => 'required|date',
        ]);

        $task->update(['due_date' => $validated['due_date']]);

        return back()->with('success', 'Görev yeniden planlandı.');
    }
}
