<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PresenceController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('chat.index')
        : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'loginStore'])->name('login.store');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'registerStore'])->name('register.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/with/{user}', [ChatController::class, 'withUser'])->name('chat.with');
    Route::get('/chat/room/{room}', [ChatController::class, 'room'])->name('chat.room');
    Route::post('/chat/room/{room}/messages', [ChatController::class, 'storeMessage'])->name('messages.store');
    Route::get('/chat/room/{room}/fetch-messages', [ChatController::class, 'fetchMessages'])->name('messages.fetch');

    Route::post('/presence/online', [PresenceController::class, 'online'])->name('presence.online');
    Route::post('/presence/offline', [PresenceController::class, 'offline'])->name('presence.offline');
    Route::get('/users-status', [PresenceController::class, 'statuses'])->name('users.status');

    Route::resource('/contacts', ContactController::class);

    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');

    Route::get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{id}/send', [GroupController::class, 'sendMessage'])->name('groups.send');

    Route::get('/groups/{id}/add-member', [GroupController::class, 'addMember'])->name('groups.addMember');
    Route::post('/groups/{id}/add-member', [GroupController::class, 'storeMember'])->name('groups.storeMember');
});