<?php

namespace Modules\Integrations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    private string $note = 'Bu özellik aktif değil. İlgili API anahtarını ayarlardan ekleyin.';

    public function smsIndex()
    {
        return view('integrations::communication.sms', ['note' => $this->note]);
    }

    public function sendSms(Request $request)
    {
        $request->validate(['phone' => 'required|string', 'message' => 'required|string|max:160']);

        return response()->json(['success' => false, 'message' => $this->note]);
    }

    public function sendBulkSms(Request $request)
    {
        return response()->json(['success' => false, 'message' => $this->note]);
    }

    public function whatsappIndex()
    {
        return view('integrations::communication.whatsapp', ['note' => $this->note]);
    }

    public function sendWhatsapp(Request $request)
    {
        return response()->json(['success' => false, 'message' => $this->note]);
    }

    public function sendWhatsappTemplate(Request $request)
    {
        return response()->json(['success' => false, 'message' => $this->note]);
    }

    public function callsIndex()
    {
        return view('integrations::communication.calls', ['note' => $this->note]);
    }

    public function initiateCall(Request $request)
    {
        return response()->json(['success' => false, 'message' => $this->note]);
    }

    public function getRecording($activity)
    {
        return response()->json(['success' => false, 'message' => $this->note]);
    }

    public function transcribeCall($activity)
    {
        return response()->json(['success' => false, 'message' => $this->note]);
    }
}
