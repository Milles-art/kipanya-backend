<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class ControlPanelController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function module(string $module): View
    {
        abort_unless(in_array($module, ['cartoon', 'wear', 'book', 'motors', 'tv'], true), 404);

        return view('admin.modules.placeholder', [
            'module' => $module,
        ]);
    }
}
