<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Message;
use Modules\Integrations\Channels\ChannelManager;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $channelFilter = $request->query('channel');
        $statusFilter = $request->query('status', 'open');

        $query = Conversation::query()
            ->with(['contact', 'lead', 'assignee'])
            ->orderByDesc('last_message_at');

        if ($officeId = $request->user()->office_id ?? null) {
            $query->where('office_id', $officeId);
        }
        if ($channelFilter) {
            $query->where('channel', $channelFilter);
        }
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $conversations = $query->limit(200)->get();

        $counts = [
            'open' => Conversation::where('status', 'open')->count(),
            'archived' => Conversation::where('status', 'archived')->count(),
            'closed' => Conversation::where('status', 'closed')->count(),
            'unread' => Conversation::where('status', 'open')->where('unread_count', '>', 0)->count(),
        ];

        $channels = app(ChannelManager::class)->all();

        return view('crm::inbox.index', [
            'conversations' => $conversations,
            'counts' => $counts,
            'channels' => $channels,
            'activeChannel' => $channelFilter,
            'activeStatus' => $statusFilter,
        ]);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorizeView($request, $conversation);

        $conversation->load(['messages.sentByUser', 'contact', 'lead', 'assignee']);
        $conversation->markAsRead();

        $agentsQuery = User::query()->where('is_active', true)->orderBy('name');
        if ($officeId = $request->user()->office_id ?? null) {
            $agentsQuery->where('office_id', $officeId);
        }
        $agents = $agentsQuery->get(['id', 'name']);

        return view('crm::inbox.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages,
            'agents' => $agents,
        ]);
    }

    public function send(Request $request, Conversation $conversation, ChannelManager $channels)
    {
        $this->authorizeView($request, $conversation);

        $data = $request->validate([
            'body' => 'required|string|max:4000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string',
        ]);

        if (!$channels->has($conversation->channel)) {
            return back()->withErrors(['channel' => 'Kanal kayıtlı değil: ' . $conversation->channel]);
        }

        $channel = $channels->get($conversation->channel);
        if (!$channel->isEnabled()) {
            return back()->withErrors(['channel' => $conversation->channel . ' kanalı yapılandırılmamış.']);
        }

        try {
            $message = $channel->send(
                $conversation,
                $data['body'],
                $data['attachments'] ?? [],
                $request->user()->id,
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', 'Mesaj gönderildi');
        } catch (\Throwable $e) {
            return back()->withErrors(['send' => 'Gönderim başarısız: ' . $e->getMessage()]);
        }
    }

    public function assign(Request $request, Conversation $conversation)
    {
        $this->authorizeView($request, $conversation);

        $data = $request->validate([
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $conversation->update(['assigned_to' => $data['assigned_to']]);

        return back()->with('success', 'Atama güncellendi');
    }

    public function updateStatus(Request $request, Conversation $conversation)
    {
        $this->authorizeView($request, $conversation);

        $data = $request->validate([
            'status' => 'required|in:open,archived,closed',
        ]);

        $conversation->update(['status' => $data['status']]);

        return back()->with('success', 'Durum güncellendi');
    }

    protected function authorizeView(Request $request, Conversation $conversation): void
    {
        $officeId = $request->user()->office_id ?? null;
        if ($officeId && $conversation->office_id && $conversation->office_id !== $officeId) {
            abort(403);
        }
    }
}
