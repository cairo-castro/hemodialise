<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update user's basic profile information
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->save();

            // Reload user with relationships
            $user->load('unit', 'units');

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'unit_id' => $user->unit_id,
                    'unit' => $user->unit ? [
                        'id' => $user->unit->id,
                        'name' => $user->unit->name,
                    ] : null,
                    'units' => $user->units->map(fn($unit) => [
                        'id' => $unit->id,
                        'name' => $unit->name,
                    ]),
                    'created_at' => $user->created_at->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating profile: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil.'
            ], 500);
        }
    }

    /**
     * Update user's password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'A senha atual está incorreta.'
            ], 422);
        }

        // Check if new password is different from current
        if (Hash::check($request->input('new_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'A nova senha deve ser diferente da senha atual.'
            ], 422);
        }

        try {
            $user->password = Hash::make($request->input('new_password'));
            $user->save();

            \Log::info('User password updated', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating password: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar senha.'
            ], 500);
        }
    }
}
