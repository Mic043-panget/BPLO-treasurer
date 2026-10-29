<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Support\TreasurerDemoData;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return $this->page('dashboard');
    }

    public function payments(): View
    {
        return $this->page('payments');
    }

    public function history(): View
    {
        return $this->page('history');
    }

    private function page(string $activePage): View
    {
        return view('treasurer.dashboard', [
            'activePage' => $activePage,
            'payments' => TreasurerDemoData::payments(),
            'history' => TreasurerDemoData::history(),
        ]);
    }
}
