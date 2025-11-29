<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\ModelController;
use App\Http\Controllers\Master\SubcategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Middleware\IsAdminOrSuperAdmin;
use App\Http\Middleware\IsSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware([IsSuperAdmin::class])->group(function () {
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('users', UserController::class);

        // Masters Management
        Route::prefix('masters')->name('masters.')->group(function () {
            Route::resource('categories', CategoryController::class);
            Route::resource('subcategories', SubcategoryController::class);
            Route::resource('models', ModelController::class);
        });
    });

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/deduct', [InventoryController::class, 'deduct'])->name('inventory.deduct');
    Route::get('/inventory/subcategories/{categoryId}', [InventoryController::class, 'getSubcategories']);
    Route::get('/inventory/models/{subcategoryId}', [InventoryController::class, 'getModels']);

    Route::middleware([IsSuperAdmin::class])->group(function () {
        Route::post('/inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');
    });

    Route::middleware([IsAdminOrSuperAdmin::class])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/filter', [ReportController::class, 'filterReports'])->name('reports.filter');
    });
});
