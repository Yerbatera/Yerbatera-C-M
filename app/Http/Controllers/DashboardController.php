<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function gerente() { return view('dashboards.gerente'); }
    public function admin() { return view('dashboards.admin'); }
    public function operario() { return view('dashboards.operario'); }
    public function yerbatero() { return view('dashboards.yerbatero'); }
}