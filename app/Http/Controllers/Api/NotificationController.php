<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Get user's current unit
        $unitId = session('current_unit_id') ?? $user->unit_id;

        // Build query
        $query = Notification::query()
            ->forUser($user->id)
            ->recent()
            ->orderBy('created_at', 'desc');

        // Filter by unit if user has one
        if ($unitId) {
            $query->forUnit($unitId);
        }

        // Apply filters
        if ($request->has('unread_only') && $request->unread_only) {
            $query->unread();
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        // Get unread count
        $unreadCount = Notification::query()
            ->forUser($user->id)
            ->when($unitId, fn($q) => $q->forUnit($unitId))
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications->items(),
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get unread count only (lightweight for polling)
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $unitId = session('current_unit_id') ?? $user->unit_id;

        $count = Notification::query()
            ->forUser($user->id)
            ->when($unitId, fn($q) => $q->forUnit($unitId))
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Get recent notifications for polling (last 5 minutes)
     */
    public function poll(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $unitId = session('current_unit_id') ?? $user->unit_id;

        // Get notifications from the last 5 minutes
        $lastCheck = $request->get('last_check', now()->subMinutes(5)->toIso8601String());

        $notifications = Notification::query()
            ->forUser($user->id)
            ->when($unitId, fn($q) => $q->forUnit($unitId))
            ->where('created_at', '>', $lastCheck)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = Notification::query()
            ->forUser($user->id)
            ->when($unitId, fn($q) => $q->forUnit($unitId))
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notification = Notification::query()
            ->forUser($user->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $unitId = session('current_unit_id') ?? $user->unit_id;

        $updated = Notification::query()
            ->forUser($user->id)
            ->when($unitId, fn($q) => $q->forUnit($unitId))
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'count' => $updated,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notification = Notification::query()
            ->forUser($user->id)
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
