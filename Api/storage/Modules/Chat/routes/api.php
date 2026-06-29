<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Chat\app\Http\Controllers\Api\AdminChatManageController;
use Modules\Chat\app\Http\Controllers\Api\ChatController;
use Modules\Chat\app\Http\Controllers\ChatManageController;


//  Admin Chat manage
Route::middleware(['auth:sanctum'])->prefix('v1/admin/chat/')->group(function () {
    Route::prefix('settings')->middleware(['permission:' . PermissionKey::ADMIN_CHAT_SETTINGS->value])->group(function () {
        Route::match(['get', 'post'], '/', [AdminChatManageController::class, 'chatPusherSettings']);
    });
    // prefix manage
    Route::prefix('manage')->middleware(['permission:' . PermissionKey::ADMIN_CHAT_MANAGE->value])->group(function () {
    Route::get('list/', [ChatController::class, 'chatList']);
    Route::post('send', [ChatController::class, 'sendMessage']);
    Route::get('messages-details/{chatId}', [ChatController::class, 'chatWiseFetchMessages']);
    Route::post('chat/seen', [ChatController::class, 'markAsSeen']);
    });
});

//  Seller Chat manage
Route::middleware(['auth:sanctum'])->prefix('v1/seller/store/chat/')->group(function () {
    Route::get('list/', [ChatController::class, 'chatList']);
    Route::post('send', [ChatController::class, 'sendMessage']);
    Route::get('messages-details/{chatId}', [ChatController::class, 'chatWiseFetchMessages']);
    Route::post('chat/seen', [ChatController::class, 'markAsSeen']);
});

//  Customer Chat manage
Route::middleware(['auth:sanctum'])->prefix('v1/customer/chat/')->group(function () {
    Route::get('list/', [ChatController::class, 'chatList']);
    Route::post('send', [ChatController::class, 'sendMessage']);
    Route::get('messages-details/{chatId}', [ChatController::class, 'chatWiseFetchMessages']);
    Route::post('chat/seen', [ChatController::class, 'markAsSeen']);
});

//  deliveryman Chat manage
Route::middleware(['auth:sanctum'])->prefix('v1/deliveryman/chat/')->group(function () {
    Route::get('list/', [ChatController::class, 'chatList']);
    Route::post('send', [ChatController::class, 'sendMessage']);
    Route::get('messages-details/{chatId}', [ChatController::class, 'chatWiseFetchMessages']);
    Route::post('chat/seen', [ChatController::class, 'markAsSeen']);
});


// pusher info
Route::middleware(['auth:sanctum'])->prefix('v1/')->group(function () {
    Route::get('/chat-credentials', [ChatManageController::class, 'getChatCredentials']);
});
