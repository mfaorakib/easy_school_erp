<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\Http\Controllers\ChatController;
use Modules\Chat\Http\Controllers\ContactController;
use Modules\Chat\Http\Controllers\GroupChatController;
use Modules\Chat\Http\Controllers\GroupManageController;

Route::middleware('auth')->prefix('chat')->name('chat.')->group(function () {
    // --- Direct messaging (Agent A) ---
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('with/{user}', [ChatController::class, 'show'])->name('show');
    Route::post('with/{user}', [ChatController::class, 'send'])->name('send');

    // --- Contacts + block/unblock (Agent D) ---
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts');
    Route::post('block/{user}', [ContactController::class, 'block'])->name('block');
    Route::delete('block/{user}', [ContactController::class, 'unblock'])->name('unblock');

    // --- Group threads (Agent B) ---
    Route::get('groups', [GroupChatController::class, 'index'])->name('groups.index');

    // --- Group management (Agent C) --- (declare create/store before {group} routes) ---
    Route::get('groups-create', [GroupManageController::class, 'create'])->name('groups.create');
    Route::post('groups-create', [GroupManageController::class, 'store'])->name('groups.store');
    Route::get('groups/{group}/members', [GroupManageController::class, 'members'])->name('groups.members');
    Route::post('groups/{group}/members', [GroupManageController::class, 'addMember'])->name('groups.members.add');
    Route::put('groups/{group}/members/{user}', [GroupManageController::class, 'setRole'])->name('groups.members.role');
    Route::delete('groups/{group}/members/{user}', [GroupManageController::class, 'removeMember'])->name('groups.members.remove');

    // --- Group thread (Agent B) --- ({group} routes last so they don't shadow the above) ---
    Route::get('groups/{group}', [GroupChatController::class, 'show'])->name('groups.show');
    Route::post('groups/{group}', [GroupChatController::class, 'send'])->name('groups.send');
});
