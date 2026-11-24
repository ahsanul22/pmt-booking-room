<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['organizationalUnit', 'branches']);

        return view('admin.dashboard', app(AdminDashboard::class)->data($user) + [
            'user' => $user,
            'scopeLabel' => $user->isSuperAdmin() ? 'Semua lokasi' : ($user->branches->pluck('name')->implode(', ') ?: 'Lokasi belum diatur'),
            'title' => 'Dashboard Admin',
        ]);
    }
}
