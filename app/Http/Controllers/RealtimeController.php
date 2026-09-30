<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    public function stream(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'notifications' => $this->notificationsPayload($user),
            'heartbeat'     => ['time' => now()->toISOString()],
        ]);
    }

    private function notificationsPayload(User $user): array
    {
        $query = Notification::where('user_id', $user->id)->latest();

        return [
            'items' => (clone $query)->limit(8)->get(),
            'unread_count' => (clone $query)->where('lu', false)->count(),
            'meta' => [
                'generated_at' => now()->toISOString(),
            ],
        ];
    }
}
