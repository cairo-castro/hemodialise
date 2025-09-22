<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function ionic()
    {
        // Ionic Vue interface - única interface mobile
        return view('mobile.ionic', [
            'title' => 'Sistema de Hemodiálise - Mobile',
            'interface_type' => 'ionic'
        ]);
    }
}
