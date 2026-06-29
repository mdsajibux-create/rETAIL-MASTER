<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\Customer\CustomerSupportTicketManageController;
use Illuminate\Support\Facades\Route;
use Modules\SupportTicket\app\Http\Controllers\Api\V1\AdminSupportTicketManageController;
use Modules\SupportTicket\app\Http\Controllers\Api\V1\DepartmentManageController;


/*---------------------  admin ticket management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    Route::group(['prefix' => 'support-ticket/', 'middleware' => 'permission:' . PermissionKey::ADMIN_SUPPORT_TICKETS_MANAGE->value], function () {
        Route::get('list', [AdminSupportTicketManageController::class, 'index']);
        Route::get('details/{id?}', [AdminSupportTicketManageController::class, 'show']);
        Route::post('change-priority-status', [AdminSupportTicketManageController::class, 'changePriorityStatus']);
        Route::post('resolve', [AdminSupportTicketManageController::class, 'resolve']);
        Route::post('message/reply', [AdminSupportTicketManageController::class, 'replyMessage']);
        Route::get('get-ticket-messages/{ticket_id}', [AdminSupportTicketManageController::class, 'getTicketMessages']);
        Route::post('remove', [AdminSupportTicketManageController::class, 'destroy']);
    });

    // Department manage
    Route::group(['prefix' => 'department/'], function () {
        Route::get('list', [DepartmentManageController::class, 'listDepartments']);
        Route::post('add', [DepartmentManageController::class, 'createDepartment']);
        Route::get('details/{id}', [DepartmentManageController::class, 'getDepartmentById']);
        Route::post('update', [DepartmentManageController::class, 'updateDepartment']);
        Route::delete('remove/{id}', [DepartmentManageController::class, 'deleteDepartment']);
    });
});

/*---------------------  customer ticket management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/customer/')->group(function () {
    Route::group(['prefix' => 'support-ticket', 'middleware' => ['auth:api_customer', 'check.customer.account.status', 'detect.platform']], function () {
        Route::group(['middleware' => ['check.email.verification.option:customer']], function () {
            Route::get('list', [CustomerSupportTicketManageController::class, 'listSupportTickets']);
            Route::post('store', [CustomerSupportTicketManageController::class, 'createSupportTicket']);
            Route::post('update', [CustomerSupportTicketManageController::class, 'updateSupportTicket']);
            Route::get('details/{ticket_id}', [CustomerSupportTicketManageController::class, 'getSupportTicketById']);
            Route::patch('resolve', [CustomerSupportTicketManageController::class, 'resolveSupportTicket']);
            Route::post('add-message', [CustomerSupportTicketManageController::class, 'addTicketMessage']);
            Route::get('messages/{ticket_id}', [CustomerSupportTicketManageController::class, 'ticketMessages']);
        });
    });
});
