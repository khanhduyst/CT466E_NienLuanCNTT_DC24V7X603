<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin/cau-hinh', function () {
        return view('admin.setting');
    })->middleware('role:super_admin');

    Route::get('/admin/danh-muc', [CategoryController::class, 'index'])->name('category.index')->middleware('role:super_admin');
    Route::post('/admin/danh-muc', [CategoryController::class, 'store'])->middleware('role:super_admin');
    Route::put('/admin/danh-muc/{id}', [CategoryController::class, 'update'])->middleware('role:super_admin');
    Route::delete('/admin/danh-muc/{id}', [CategoryController::class, 'destroy'])->middleware('role:super_admin');


    Route::get('/admin/san-pham', [ProductController::class, 'index']);
    Route::post('/admin/san-pham', [ProductController::class, 'store']);
    Route::put('/admin/san-pham/{id}', [ProductController::class, 'update']);
    Route::delete('/admin/san-pham/{id}', [ProductController::class, 'destroy']);

    Route::get('/admin/nhap-kho', [PurchaseController::class, 'index'])->name('admin.purchase.index');
    Route::post('/admin/nhap-kho', [PurchaseController::class, 'store'])->name('admin.purchase.store');
    Route::post('/admin/nha-cung-cap/them', [PurchaseController::class, 'storeSupplier'])->name('admin.supplier.store');
    Route::post('/admin/nha-cung-cap/sua/{id}', [PurchaseController::class, 'updateSupplier'])->name('admin.supplier.update');
    Route::delete('/admin/nha-cung-cap/xoa/{id}', [PurchaseController::class, 'destroySupplier'])->name('admin.supplier.destroy');
    Route::get('/admin/nha-cung-cap', [PurchaseController::class, 'indexSupplier'])->name('admin.supplier.index');

    Route::get('/admin/lich-su-nhap-kho', [PurchaseController::class, 'history'])->name('admin.purchase.history');
    // Route::get('/kho/kiem-hang', function () {
    //     return view('inventory.index');
    // })->middleware('role:super_admin,manager');

    // Route::get('/pos/ban-hang', function () {
    //     return view('pos.index');
    // })->middleware('role:super_admin,manager,staff');
});
