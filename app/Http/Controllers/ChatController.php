<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatRoom;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        $users = User::where('id', '!=', $currentUser->id)
            ->orderBy('name', 'asc')
            ->get();

        $groups = Group::with('members')
            ->whereHas('members', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->latest()
            ->get();

        return view('chat.index', compact('users', 'groups'));
    }

    public function withUser(User $user)
    {
        $currentUser = Auth::user();

        $room = ChatRoom::where(function ($query) use ($currentUser, $user) {
                $query->where('user_one_id', $currentUser->id)
                    ->where('user_two_id', $user->id);
            })
            ->orWhere(function ($query) use ($currentUser, $user) {
                $query->where('user_one_id', $user->id)
                    ->where('user_two_id', $currentUser->id);
            })
            ->first();

        if (!$room) {
            $room = ChatRoom::create([
                'user_one_id' => $currentUser->id,
                'user_two_id' => $user->id,
            ]);
        }

        return redirect()->route('chat.room', $room->id);
    }

    public function room($roomId)
    {
        $currentUser = Auth::user();

        $room = ChatRoom::with(['userOne', 'userTwo'])
            ->findOrFail($roomId);

        if (
            $room->user_one_id !== $currentUser->id &&
            $room->user_two_id !== $currentUser->id
        ) {
            abort(403);
        }

        $chatUser = $room->user_one_id === $currentUser->id
            ? $room->userTwo
            : $room->userOne;

        $users = User::where('id', '!=', $currentUser->id)
            ->orderBy('name', 'asc')
            ->get();

        $groups = Group::with('members')
            ->whereHas('members', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->latest()
            ->get();

        $messages = Message::with('user')
            ->where('chat_room_id', $room->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.room', compact(
            'room',
            'chatUser',
            'users',
            'groups',
            'messages'
        ));
    }

    public function storeMessage(Request $request, $roomId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $room = ChatRoom::findOrFail($roomId);

        if (
            $room->user_one_id !== Auth::id() &&
            $room->user_two_id !== Auth::id()
        ) {
            abort(403);
        }

        $message = Message::create([
            'chat_room_id' => $room->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        $message->load('user');

        broadcast(new MessageSent($message))->toOthers();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('chat.room', $room->id)
            ->with('success', 'Pesan berhasil dikirim');
    }

    public function fetchMessages($roomId)
    {
        $room = ChatRoom::findOrFail($roomId);

        if (
            $room->user_one_id !== Auth::id() &&
            $room->user_two_id !== Auth::id()
        ) {
            abort(403);
        }

        $messages = Message::with('user')
            ->where('chat_room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}