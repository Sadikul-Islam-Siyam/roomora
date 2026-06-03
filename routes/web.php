<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════
// PUBLIC ROUTES
// ═══════════════════════════════════════════════════════
Route::get('/', fn() => redirect()->route('hotels.index'))->name('home');

// Hotels (public)
Route::prefix('hotels')->name('hotels.')->group(function () {
    Route::get('/',            [HotelController::class, 'index'])->name('index');
    Route::get('/search',      [HotelController::class, 'searchSuggestions'])->name('search');
    Route::get('/{hotel}',     [HotelController::class, 'show'])->name('show');
});

// ═══════════════════════════════════════════════════════
// AUTH ROUTES (guests only)
// ═══════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ═══════════════════════════════════════════════════════
// AUTHENTICATED ROUTES
// ═══════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',            [ProfileController::class, 'show'])->name('show');
        Route::get('/edit',        [ProfileController::class, 'edit'])->name('edit');
        Route::put('/',            [ProfileController::class, 'update'])->name('update');
        Route::put('/password',    [ProfileController::class, 'updatePassword'])->name('password');
        Route::get('/bookings',    [ProfileController::class, 'bookingHistory'])->name('bookings');
    });

    // Bookings
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/room/{room}',     [BookingController::class, 'create'])->name('create');
        Route::post('/',               [BookingController::class, 'store'])->middleware('throttle:booking')->name('store');
        Route::get('/{booking}',       [BookingController::class, 'show'])->name('show');
        Route::post('/{booking}/pay',  [BookingController::class, 'pay'])->name('pay');
        Route::post('/{booking}/guests', [BookingController::class, 'updateGuests'])->name('guests.update');
        Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        Route::get('/{booking}/invoice', [BookingController::class, 'downloadInvoice'])->name('invoice');
    });

    // AJAX: Check availability
    Route::post('/rooms/{room}/availability', [BookingController::class, 'checkAvailability'])
        ->name('rooms.availability');

    // Reviews
    Route::post('/hotels/{hotel}/reviews',      [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}',             [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}',          [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Comparisons
    Route::prefix('compare')->name('comparisons.')->group(function () {
        Route::get('/',                        [ComparisonController::class, 'index'])->name('index');
        Route::post('/toggle/{hotel}',         [ComparisonController::class, 'toggle'])->name('toggle');
        Route::delete('/clear',                [ComparisonController::class, 'clear'])->name('clear');
    });

    // Favorites / Wishlist
    Route::prefix('favorites')->name('favorites.')->group(function () {
        Route::get('/',                        [FavoriteController::class, 'index'])->name('index');
        Route::post('/toggle/{hotel}',         [FavoriteController::class, 'toggle'])->name('toggle');
    });
});

// ═══════════════════════════════════════════════════════
// ADMIN ROUTES
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/',           [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats',      [DashboardController::class, 'refreshStats'])->name('stats');

    // Hotels CRUD
    Route::resource('hotels', AdminHotelController::class);
    Route::post('/hotels/{hotel}/toggle', [AdminHotelController::class, 'toggle'])->name('hotels.toggle');

    // Rooms CRUD (nested under hotels)
    Route::resource('hotels.rooms', AdminRoomController::class)->shallow();

    // Users
    Route::resource('users', AdminUserController::class)->except(['create', 'store']);
    Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');

    // Bookings
    Route::get('/bookings',              [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}',    [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');

    // Reports
    Route::get('/reports',               [AdminBookingController::class, 'reports'])->name('reports');
    Route::get('/reports/export',        [AdminBookingController::class, 'export'])->name('reports.export');
});
