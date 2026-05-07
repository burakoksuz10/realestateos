<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return $this->paginated(
            $request->user()->notifications()->paginate(20)
        );
    }

    public function unreadCount(Request $request)
    {
        return $this->success([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->success(null, 'Bildirim okundu olarak işaretlendi.');
    }

    public function markAsRead(Request $request, string $id)
    {
        return $this->read($request, $id);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success(null, 'Tüm bildirimler okundu olarak işaretlendi.');
    }

    public function markAllAsRead(Request $request)
    {
        return $this->readAll($request);
    }
}
