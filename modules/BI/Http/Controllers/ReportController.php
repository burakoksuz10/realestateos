<?php

namespace Modules\BI\Http\Controllers;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function conversionFunnel()
    {
        return view('bi::reports.stub', ['title' => 'Dönüşüm Hunisi']);
    }

    public function agentPerformance()
    {
        return view('bi::reports.stub', ['title' => 'Danışman Performansı']);
    }

    public function leadSources()
    {
        return view('bi::reports.stub', ['title' => 'Lead Kaynakları']);
    }

    public function portalPerformance()
    {
        return view('bi::reports.stub', ['title' => 'Portal Performansı']);
    }

    public function listingPerformance()
    {
        return view('bi::reports.stub', ['title' => 'İlan Performansı']);
    }

    public function revenue()
    {
        return view('bi::reports.stub', ['title' => 'Gelir Raporu']);
    }

    public function custom()
    {
        return view('bi::reports.stub', ['title' => 'Özel Rapor']);
    }

    public function export(string $report)
    {
        return response()->json(['success' => false, 'message' => 'Rapor dışa aktarımı henüz aktif değil.']);
    }
}
