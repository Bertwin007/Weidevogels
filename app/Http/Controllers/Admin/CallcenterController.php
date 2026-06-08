<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CallcenterQueueService;
use Illuminate\View\View;

class CallcenterController extends Controller
{
    public function index(CallcenterQueueService $callcenter): View
    {
        return view('admin.callcenter', [
            'kpis' => $callcenter->kpis(),
            'queue' => $callcenter->queue(),
        ]);
    }
}
