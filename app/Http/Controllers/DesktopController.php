<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DesktopController extends Controller
{
    public function index()
    {
        return view('desktop.index', [
            'title' => 'Sistema de Hemodiálise - Desktop',
            'interface_type' => 'vue'
        ]);
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

    public function preline()
    {
        // Preline-enhanced desktop interface
        return view('desktop.preline', [
            'title' => 'Sistema de Hemodiálise - Desktop',
            'interface_type' => 'preline'
        ]);
    }

    public function simple()
    {
        // Simple desktop interface for testing
        return view('desktop.simple', [
            'title' => 'Sistema de Hemodiálise - Desktop (Teste)',
            'interface_type' => 'simple'
        ]);
    }
}