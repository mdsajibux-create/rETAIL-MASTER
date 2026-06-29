<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Blog\app\Http\Controllers\Api\AdminBlogManageController;
use Modules\Blog\app\Http\Controllers\Api\FrontendBlogController;


// admin blog manage
Route::middleware(['auth:sanctum'])->prefix('v1/')->group(function () {
    Route::group(['prefix' => 'admin/blog', 'middleware' => ['permission:' . PermissionKey::ADMIN_BLOG_MANAGE->value]], function () {
        Route::get('list', [AdminBlogManageController::class, 'listBlogs']);
        Route::post('add', [AdminBlogManageController::class, 'createBlog']);
        Route::get('details/{id}', [AdminBlogManageController::class, 'getBlogById']);
        Route::post('update', [AdminBlogManageController::class, 'updateBlog']);
        Route::patch('change-status', [AdminBlogManageController::class, 'changeBlogStatus']);
        Route::post('remove', [AdminBlogManageController::class, 'deleteBlogs']);
        // Blog category manage
        Route::group(['prefix' => 'category/', 'middleware' => ['permission:' . PermissionKey::ADMIN_BLOG_CATEGORY_MANAGE->value]], function () {
            Route::get('list', [AdminBlogManageController::class, 'blogCategoryIndex']);
            Route::get('fetch/list', [AdminBlogManageController::class, 'blogCategoryList']);
            Route::post('add', [AdminBlogManageController::class, 'blogCategoryStore']);
            Route::get('details/{id}', [AdminBlogManageController::class, 'blogCategoryShow']);
            Route::post('update', [AdminBlogManageController::class, 'blogCategoryUpdate']);
            Route::post('change-status', [AdminBlogManageController::class, 'categoryStatusChange']);
            Route::delete('remove/{id}', [AdminBlogManageController::class, 'blogCategoryDestroy']);
        });
    });
});

// public blog routes
Route::group(['prefix' => 'v1/'], function () {
    Route::get('/blogs', [FrontendBlogController::class, 'blogs']);
    Route::get('/blog/{slug}', [FrontendBlogController::class, 'blogDetails']);
    Route::get('/blog-page-settings', [FrontendBlogController::class, 'BlogPageSettings']);
});