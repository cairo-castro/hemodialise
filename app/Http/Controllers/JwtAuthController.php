<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthController extends Controller
{
    public function check(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['authenticated' => false], 401);
            }

            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'unit_id' => $user->unit_id,
                    'unit' => $user->unit,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['authenticated' => false], 401);
        }
    }
}