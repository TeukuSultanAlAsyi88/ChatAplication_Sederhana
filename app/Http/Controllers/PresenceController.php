<?php

namespace App\Http\Controllers;

use App\Events\UserStatusChanged;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceController extends Controller
{
    public function online(Request $request)
    {
        $user = $request->user();

        $user->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        broadcast(new UserStatusChanged($user))->toOthers();

        return response()->json(['success' => true]);
    }

    public function offline(Request $request)
    {
        $user = $request->user();

        $user->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);

        broadcast(new UserStatusChanged($user))->toOthers();

        return response()->json(['success' => true]);
    }

    public function statuses()
    {
        return response()->json(
            User::select('id', 'name', 'is_online', 'last_seen_at')
                ->orderBy('name')
                ->get()
        );
    }
}
