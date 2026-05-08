<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $openaiKey = Setting::get('openai_api_key', '');
        $openaiModel = Setting::get('openai_model', 'gpt-4o-mini');
        $newsEnabled = Setting::get('news_enabled', '1');

        return view('core::settings.index', compact('openaiKey', 'openaiModel', 'newsEnabled'));
    }

    public function general()
    {
        return view('core::settings.general');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name'    => 'nullable|string|max:255',
            'company_email'   => 'nullable|email|max:255',
            'company_phone'   => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'timezone'        => 'nullable|string',
            'currency'        => 'nullable|string|size:3',
            'language'        => 'nullable|string|size:2',
            'openai_api_key'  => 'nullable|string|max:255',
            'openai_model'    => 'nullable|string|max:100',
            'news_enabled'    => 'nullable|boolean',
        ]);

        foreach (['openai_api_key', 'openai_model', 'news_enabled'] as $key) {
            if (array_key_exists($key, $validated)) {
                Setting::set($key, $validated[$key] ?? '');
            }
        }

        return back()->with('success', 'Ayarlar başarıyla güncellendi.');
    }

    public function notifications()
    {
        return view('core::settings.notifications');
    }

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications'  => 'boolean',
            'sms_notifications'   => 'boolean',
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

    public function profile(Request $request)
    {
        return view('core::settings.index');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Profil başarıyla güncellendi.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update(['password' => bcrypt($request->password)]);

        return back()->with('success', 'Şifre başarıyla güncellendi.');
    }
}
