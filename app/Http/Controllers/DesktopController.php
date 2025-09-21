<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DesktopController extends Controller
{
    public function index()
    {
        return view('desktop.index');
    }

    public function dashboard()
    {
        return view('desktop.dashboard');
    }

    public function reports()
    {
        return view('desktop.reports');
    }

    public function analytics()
    {
        return view('desktop.analytics');
    }

    public function settings()
    {
        return view('desktop.settings');
    }
}