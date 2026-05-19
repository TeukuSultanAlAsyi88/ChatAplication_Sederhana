<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Models\ChatGroup;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $groups = ChatGroup::with('members')
            ->latest()
            ->get();

        $users = User::where('id', '!=', Auth::id())->get();

        return view('groups.index', compact('groups', 'users'));
    }

    public function create()
    {
        return redirect()->route('groups.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('groups.index');
    }

    public function show($id)
    {
        $group = ChatGroup::with('members')->findOrFail($id);

        if (!$group->members()->where('users.id', Auth::id())->exists()) {
            $group->members()->syncWithoutDetaching([Auth::id()]);
        }

        $members = $group->members()->get();

        $messages = GroupMessage::with('user')
            ->where('group_id', $group->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('groups.chat', compact('group', 'members', 'messages'));
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $group = ChatGroup::findOrFail($id);

        if (!$group->members()->where('users.id', Auth::id())->exists()) {
            $group->members()->syncWithoutDetaching([Auth::id()]);
        }

        $message = GroupMessage::create([
            'group_id' => $group->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        $message->load('user');

        broadcast(new GroupMessageSent($message))->toOthers();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('groups.show', $group->id);
    }

    public function addMember($id)
    {
        return redirect()->route('groups.show', $id);
    }

    public function storeMember(Request $request, $id)
    {
        return redirect()->route('groups.show', $id);
    }
}