<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class AdminDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        $adminId = auth()->id();
        $data = $this->dashboardService->getDashboardData($adminId);

        return view('admin.dashboard.index', $data);
    }
}
