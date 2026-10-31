<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\PoInfoController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\CancellationController as AdminCancellationController;

use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\PoPeriodController as OwnerPoPeriodController;
use App\Http\Controllers\Owner\ProductController as OwnerProductController;
use App\Http\Controllers\Owner\RecapController as OwnerRecapController;
use App\Http\Controllers\Owner\CustomerController as OwnerCustomerController;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Pre-Order Parfum Uyuy Jastip
|--------------------------------------------------------------------------
|
| Role-based access control:
| - Public: Home, Catalog, Product Detail, PO Info, Login, Register
| - Customer: Dashboard, Cart, Checkout, My Orders, Payment Upload, Cancellation
| - Admin: Operational Dashboard, Order List & Status, Payment Verification, Cancellation Review
| - Owner: Owner Dashboard, PO Period CRUD/Toggle, Product CRUD/Toggle, Recap, Customer DB
|
*/

// ==========================================
// 1. PUBLIC ROUTES
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/produk/{id}', [ProductDetailController::class, 'show'])->name('product.detail');
Route::get('/informasi-po', [PoInfoController::class, 'index'])->name('po.info');

// ==========================================
// 2. AUTHENTICATION ROUTES
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ==========================================
// 3. CUSTOMER PROTECTED ROUTES (role:customer)
// ==========================================
Route::prefix('pelanggan')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    // Cart & Checkout
    Route::get('/keranjang', [CustomerCartController::class, 'index'])->name('customer.cart');
    Route::post('/keranjang/tambah', [CustomerCartController::class, 'add'])->name('customer.cart.add');
    Route::post('/keranjang/hapus/{key}', [CustomerCartController::class, 'remove'])->name('customer.cart.remove');
    Route::post('/checkout', [CustomerCartController::class, 'checkout'])->name('customer.checkout.post');

    // Orders Management
    Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/pesanan/{id}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');

    // Payment Proof Upload (BCA Merchant)
    Route::get('/pesanan/{id}/pembayaran', [CustomerOrderController::class, 'payment'])->name('customer.orders.payment');
    Route::post('/pesanan/{id}/pembayaran', [CustomerOrderController::class, 'uploadPayment'])->name('customer.orders.payment.post');

    // Cancellation Request
    Route::get('/pesanan/{id}/batal', [CustomerOrderController::class, 'cancellation'])->name('customer.orders.cancellation');
    Route::post('/pesanan/{id}/batal', [CustomerOrderController::class, 'submitCancellation'])->name('customer.orders.cancellation.post');
});


// ==========================================
// 4. ADMIN PROTECTED ROUTES (role:admin)
// ==========================================
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Operational Order Management
    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/pesanan/{id}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/pesanan/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status.update');

    // Payment Verification (Manual BCA Merchant)
    Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/pembayaran/{id}/verifikasi', [AdminPaymentController::class, 'verify'])->name('admin.payments.verify');
    Route::post('/pembayaran/{id}/verifikasi', [AdminPaymentController::class, 'processVerification'])->name('admin.payments.verify.post');

    // Cancellation Review
    Route::get('/pembatalan', [AdminCancellationController::class, 'index'])->name('admin.cancellations.index');
    Route::get('/pembatalan/{id}', [AdminCancellationController::class, 'show'])->name('admin.cancellations.show');
    Route::post('/pembatalan/{id}/putusan', [AdminCancellationController::class, 'decide'])->name('admin.cancellations.decide.post');
});


// ==========================================
// 5. OWNER PROTECTED ROUTES (role:owner)
// ==========================================
Route::prefix('owner')->middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');

    // PO Periods Management (Open / Close PO)
    Route::get('/periode-po', [OwnerPoPeriodController::class, 'index'])->name('owner.po-periods.index');
    Route::get('/periode-po/tambah', [OwnerPoPeriodController::class, 'create'])->name('owner.po-periods.create');
    Route::post('/periode-po', [OwnerPoPeriodController::class, 'store'])->name('owner.po-periods.store');
    Route::post('/periode-po/{id}/status', [OwnerPoPeriodController::class, 'toggleStatus'])->name('owner.po-periods.status.toggle');

    // Product Catalog Management
    Route::get('/produk', [OwnerProductController::class, 'index'])->name('owner.products.index');
    Route::get('/produk/tambah', [OwnerProductController::class, 'create'])->name('owner.products.create');
    Route::post('/produk', [OwnerProductController::class, 'store'])->name('owner.products.store');
    Route::get('/produk/{id}/edit', [OwnerProductController::class, 'edit'])->name('owner.products.edit');
    Route::put('/produk/{id}', [OwnerProductController::class, 'update'])->name('owner.products.update');
    Route::post('/produk/{id}/toggle', [OwnerProductController::class, 'toggle'])->name('owner.products.toggle');

    // Business Recap & Customers
    Route::get('/rekapitulasi', [OwnerRecapController::class, 'index'])->name('owner.recap.index');
    Route::get('/pelanggan', [OwnerCustomerController::class, 'index'])->name('owner.customers.index');
});
