<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    /**
     * Get the authenticated user's notification preferences.
     */
    public function index()
    {
        $user = Auth::user();
        $preferences = NotificationPreference::forUser($user);

        return response()->json([
            'success' => true,
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update the authenticated user's notification preferences.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_new_checklists' => 'sometimes|boolean',
            'email_maintenance' => 'sometimes|boolean',
            'email_weekly_reports' => 'sometimes|boolean',
            'email_system_updates' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $preferences = NotificationPreference::forUser($user);

        $preferences->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Preferências de notificação atualizadas com sucesso!',
            'preferences' => $preferences->fresh(),
        ]);
    }
}
