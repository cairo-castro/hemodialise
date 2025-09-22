<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function index()
    {
        return view('mobile-home');
    }

    public function ionic()
    {
        // Ionic Vue interface for mobile devices
        return view('mobile.ionic', [
            'title' => 'Sistema de Hemodiálise - Mobile',
            'interface_type' => 'ionic'
        ]);
    }
}
