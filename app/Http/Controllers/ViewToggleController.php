<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewToggleController extends Controller
{
    public function switchToMobile(Request $request)
    {
        $user = Auth::user();

        // Verificar se usuário tem permissão para alternar views
        if (!in_array($user->role, ['gestor', 'coordenador', 'supervisor', 'admin'])) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // Salvar preferência temporária na sessão
        session(['current_view' => 'mobile']);

        return response()->json(['redirect' => '/mobile']);
    }

    public function switchToDesktop(Request $request)
    {
        $user = Auth::user();

        // Verificar se usuário tem permissão para alternar views
        if (!in_array($user->role, ['gestor', 'coordenador', 'supervisor', 'admin'])) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // Salvar preferência temporária na sessão
        session(['current_view' => 'desktop']);

        return response()->json(['redirect' => '/desktop']);
    }

    public function switchToAdmin(Request $request)
    {
        $user = Auth::user();

        // Verificar se usuário tem permissão para acessar admin
        if (!in_array($user->role, ['gestor', 'coordenador', 'supervisor', 'admin'])) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // Salvar preferência temporária na sessão
        session(['current_view' => 'admin']);

        return response()->json(['redirect' => '/admin-bridge']);
    }

    public function setDefaultView(Request $request)
    {
        $request->validate([
            'view' => 'required|in:mobile,desktop,admin'
        ]);

        $user = Auth::user();
        $user->default_view = $request->view;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function getUserViews()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Usuário não autenticado'], 401);
            }

            $availableViews = [];

            // Técnico: apenas mobile
            if ($user->role === 'tecnico') {
                $availableViews = ['mobile'];
            }
            // Outros roles: todas as views
            else {
                $availableViews = ['mobile', 'desktop', 'admin'];
            }

            // Carregar relações necessárias
            $user->load('unit');

            return response()->json([
                'user' => $user,
                'current_view' => session('current_view', $user->default_view),
                'available_views' => $availableViews,
                'can_toggle' => count($availableViews) > 1
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }
}