<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\userController;
use App\Http\Controllers\customerController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;

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

    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    Route::get('/profile/password', [UserController::class, 'showPasswordForm'])->name('profile.password');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin', [DashboardController::class, 'index']);
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/nhan-vien', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/nhan-vien/store', [UserController::class, 'store'])->name('admin.users.store');
    Route::post('/admin/nhan-vien/{id}/update', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/nhan-vien/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset');
    Route::post('/admin/nhan-vien/{id}/toggle', [UserController::class, 'toggleStatus'])->name('admin.users.toggle');

    Route::get('/admin/khach-hang', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::post('/admin/khach-hang/store', [CustomerController::class, 'store'])->name('admin.customers.store');
    Route::post('/admin/khach-hang/{id}/update', [CustomerController::class, 'update'])->name('admin.customers.update');
    Route::post('/admin/khach-hang/{id}/delete', [CustomerController::class, 'destroy'])->name('admin.customers.delete');

    Route::get('/admin/khach-hang/{id}/orders', [CustomerController::class, 'getOrders'])->name('admin.customers.orders');
    Route::get('/admin/khach-hang/{id}/debts', [CustomerController::class, 'getDebts'])->name('admin.customers.debts');
    Route::get('/admin/khach-hang/{id}/points', [CustomerController::class, 'getPoints'])->name('admin.customers.points');

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

    Route::get('/admin/pos/ban-hang', [PosController::class, 'index'])->name('admin.pos.index');
    Route::get('/admin/pos/search-products', [PosController::class, 'searchProducts'])->name('admin.pos.search');
    Route::post('/admin/pos/checkout', [PosController::class, 'checkout'])->name('admin.pos.checkout');
    Route::post('/admin/pos/add-customer', [PosController::class, 'addCustomer'])->name('admin.pos.add_customer');
    Route::post('/admin/pos/quick-customer', [PosController::class, 'quickStoreCustomer'])->name('admin.pos.quick_customer');

    Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{id}', [OrderController::class, 'show'])->name('admin.orders.show');

    Route::get('/admin/cong-no', [DebtController::class, 'index'])->name('admin.debts.index');
    Route::get('/admin/cong-no/{id}/lich-su', [DebtController::class, 'getLogs'])->name('admin.debts.logs');
    Route::post('/admin/cong-no/thanh-toan', [DebtController::class, 'payDebt'])->name('admin.debts.pay');

    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    // Route::get('/kho/kiem-hang', function () {
    //     return view('inventory.index');
    // })->middleware('role:super_admin,manager');

    // Route::get('/pos/ban-hang', function () {
    //     return view('pos.index');
    // })->middleware('role:super_admin,manager,staff');
});
