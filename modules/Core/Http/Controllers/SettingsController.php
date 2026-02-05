<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('core::settings.index');
    }

    public function general()
    {
        return view('core::settings.general');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'timezone' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'language' => 'nullable|string|size:2',
        ]);

        // Save settings logic here

        return back()->with('success', 'Ayarlar başarıyla güncellendi.');
    }

    public function notifications()
    {
        return view('core::settings.notifications');
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ]);

        return back()->with('success', 'Bildirim ayarları güncellendi.');
    }

    public function integrations()
    {
        return view('core::settings.integrations');
    }

    public function billing()
    {
        return view('core::settings.billing');
    }
}
