<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analytics)
    {
        $data = $analytics->getDashboardData();
        return view('dashboard.index', $data);
    }

    public function analysis()
    {
        return view('dashboard.analysis');
    }

    public function insights()
    {
        return view('dashboard.insights');
    }
}
