<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PassengerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SURSEA - Lantsa Hub routes
|--------------------------------------------------------------------------
| 1. Public pages (anyone can open)
| 2. Login / Register / Logout
| 3. Passenger pages (must be logged in as passenger)
| 4. Operator portal (must be logged in as operator)
| 5. Admin portal (must be logged in as admin)
*/

// 1. PUBLIC PAGES
Route::get('/', [PassengerController::class, 'home'])->name('home');
Route::get('/schedules', [PassengerController::class, 'schedules'])->name('schedules');
Route::get('/routes', [PassengerController::class, 'routeList'])->name('routes');

// 2. LOGIN / REGISTER / LOGOUT
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. PASSENGER PAGES
Route::middleware('role:passenger')->group(function () {
    Route::get('/waiting-list', [PassengerController::class, 'waitingList'])->name('waiting-list');

    Route::get('/reserve/{trip}', [PassengerController::class, 'seatForm'])->name('seat.form');
    Route::post('/reserve/{trip}', [PassengerController::class, 'seatStore'])->name('seat.store');
    Route::post('/reserve-cancel', [PassengerController::class, 'seatCancel'])->name('seat.cancel');
    Route::post('/reservation/delete', [PassengerController::class, 'reservationDelete'])->name('passenger.reservation.delete');

    Route::get('/waitlist/{trip}', [PassengerController::class, 'waitlistForm'])->name('waitlist.form');
    Route::post('/waitlist/{trip}', [PassengerController::class, 'waitlistStore'])->name('waitlist.store');
    Route::post('/waitlist-cancel', [PassengerController::class, 'waitlistCancel'])->name('waitlist.cancel');

    Route::get('/cargo/{trip}', [PassengerController::class, 'cargoForm'])->name('cargo.form');
    Route::post('/cargo/{trip}', [PassengerController::class, 'cargoStore'])->name('cargo.store');

    // Passenger Notifications
    Route::get('/notifications', [PassengerController::class, 'notifications'])->name('passenger.notifications');
    Route::post('/notifications/read-all', [PassengerController::class, 'notificationsReadAll'])->name('passenger.notifications.read-all');
    Route::post('/notifications/{id}/read', [PassengerController::class, 'notificationRead'])->name('passenger.notifications.read');

    // Passenger Profile & Modules
    Route::get('/profile', [PassengerController::class, 'profile'])->name('passenger.profile');
    Route::post('/profile', [PassengerController::class, 'profileSave'])->name('passenger.profile.save');
    Route::post('/profile/password', [PassengerController::class, 'profilePassword'])->name('passenger.password.save');
    Route::post('/profile/settings', [PassengerController::class, 'profileSettings'])->name('passenger.profile.settings');
    Route::post('/profile/transactions/{ref}/delete', [PassengerController::class, 'transactionDelete'])->name('passenger.transaction.delete');
});

// 4. OPERATOR PORTAL
Route::middleware('role:operator')->prefix('operator')->name('operator.')->group(function () {
    Route::get('/', fn () => redirect()->route('operator.dashboard'));
    Route::get('/dashboard', [OperatorController::class, 'dashboard'])->name('dashboard');

    // Vessel & Routes actions
    Route::post('/vessel-routes/request', [OperatorController::class, 'routeRequestStore'])->name('routes.request');
    Route::post('/vessel-routes/update', [OperatorController::class, 'routeUpdate'])->name('routes.update');
    Route::post('/vessel-routes/{id}/delete', [OperatorController::class, 'routeDelete'])->name('routes.delete');

    // Trip Schedule actions
    Route::post('/trip-schedule', [OperatorController::class, 'tripStore'])->name('trips.store');
    Route::post('/trip-schedule/update', [OperatorController::class, 'tripUpdate'])->name('trips.update');
    Route::post('/trip-schedule/{id}/status', [OperatorController::class, 'tripStatus'])->name('trips.status');
    Route::post('/trip-schedule/{id}/delete', [OperatorController::class, 'tripDelete'])->name('trips.delete');

    // Waiting List actions
    Route::post('/waiting-list/{ref}/promote', [OperatorController::class, 'waitlistPromote'])->name('waitlist.promote');
    Route::post('/waiting-list/{ref}/delete', [OperatorController::class, 'waitlistDelete'])->name('waitlist.delete');

    // Check-in & Manifest actions
    Route::post('/check-in/verify', [OperatorController::class, 'checkinVerify'])->name('checkin.verify');
    Route::post('/check-in/{ref}/delete', [OperatorController::class, 'checkinDelete'])->name('checkin.delete');
    Route::get('/manifest/export', [OperatorController::class, 'manifestExport'])->name('manifest.export');

    // Cargo actions
    Route::post('/cargo', [OperatorController::class, 'cargoStore'])->name('cargo.store');
    Route::post('/cargo/amount', [OperatorController::class, 'cargoAmount'])->name('cargo.amount');
    Route::post('/cargo/update', [OperatorController::class, 'cargoUpdate'])->name('cargo.update');
    Route::post('/cargo/{ref}/status', [OperatorController::class, 'cargoStatus'])->name('cargo.status');
    Route::post('/cargo/{ref}/delete', [OperatorController::class, 'cargoDelete'])->name('cargo.delete');

    // Reports, Notifications & Profile actions
    Route::get('/reports/export', [OperatorController::class, 'reportsExport'])->name('reports.export');
    Route::post('/notifications/read-all', [OperatorController::class, 'notificationsReadAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [OperatorController::class, 'notificationRead'])->name('notifications.read');

    Route::post('/profile', [OperatorController::class, 'profileSave'])->name('profile.save');
    Route::post('/profile/password', [OperatorController::class, 'profilePassword'])->name('profile.password');
    Route::post('/profile/preferences', [OperatorController::class, 'profilePreferences'])->name('profile.preferences');

    Route::get('/{module}', [OperatorController::class, 'module'])
        ->name('module')
        ->whereIn('module', [
            'vessel-routes', 'trip-schedule', 'waiting-list', 'check-in',
            'manifest', 'cargo', 'reports', 'notifications', 'profile',
        ]);
});

// 5. ADMIN PORTAL
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Operator Approvals & Route Requests
    Route::get('/approvals', [AdminController::class, 'approvals'])->name('approvals');
    Route::get('/approvals/{id}', [AdminController::class, 'approvalShow'])->name('approvals.show');
    Route::post('/approvals/{id}', [AdminController::class, 'approvalDecide'])->name('approvals.decide');
    Route::post('/approvals/{id}/delete', [AdminController::class, 'approvalDelete'])->name('approvals.delete');

    Route::post('/route-requests/{id}/decide', [AdminController::class, 'routeRequestDecide'])->name('route-requests.decide');
    Route::post('/route-requests/{id}/delete', [AdminController::class, 'routeRequestDelete'])->name('route-requests.delete');

    Route::get('/operators', [AdminController::class, 'operators'])->name('operators');
    Route::get('/operators/export', [AdminController::class, 'operatorsExport'])->name('operators.export');

    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [AdminController::class, 'reportsExport'])->name('reports.export');

    // Workspace Settings & Account Management
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'settingsSave'])->name('settings.save');
    Route::post('/settings/account', [AdminController::class, 'accountStore'])->name('settings.account.store');
    Route::post('/settings/account/{id}/delete', [AdminController::class, 'accountDelete'])->name('settings.account.delete');
    Route::post('/settings/password', [AdminController::class, 'passwordUpdate'])->name('settings.password');

    // Admin Profile
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminController::class, 'profileSave'])->name('profile.save');

    // Admin Notifications
    Route::post('/notifications/read-all', [AdminController::class, 'notificationsReadAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [AdminController::class, 'notificationRead'])->name('notifications.read');

    // Lantsa & Routes actions
    Route::post('/lantsa-routes', [AdminController::class, 'lantsaStore'])->name('lantsa.store');
    Route::post('/lantsa-routes/{id}/status', [AdminController::class, 'lantsaStatus'])->name('lantsa.status');
    Route::post('/lantsa-routes/{id}/delete', [AdminController::class, 'lantsaDelete'])->name('lantsa.delete');

    // Trip Schedule actions
    Route::get('/trip-schedule/export', [AdminController::class, 'tripsExport'])->name('trips.export');
    Route::post('/trip-schedule', [AdminController::class, 'tripStore'])->name('trips.store');
    Route::post('/trip-schedule/{id}/status', [AdminController::class, 'tripStatus'])->name('trips.status');

    // Passenger Manifest actions
    Route::get('/passenger-manifest/export', [AdminController::class, 'manifestExport'])->name('manifest.export');
    Route::post('/passenger-manifest', [AdminController::class, 'manifestStore'])->name('manifest.store');
    Route::post('/passenger-manifest/verify', [AdminController::class, 'manifestVerify'])->name('manifest.verify');
    Route::post('/passenger-manifest/{ref}/status', [AdminController::class, 'manifestStatus'])->name('manifest.status');
    Route::post('/passenger-manifest/{ref}/delete', [AdminController::class, 'manifestDelete'])->name('manifest.delete');

    // Cargo actions
    Route::get('/cargo/export', [AdminController::class, 'cargoExport'])->name('cargo.export');
    Route::post('/cargo', [AdminController::class, 'cargoStore'])->name('cargo.store');
    Route::post('/cargo/{ref}/status', [AdminController::class, 'cargoStatus'])->name('cargo.status');
    Route::post('/cargo/{ref}/delete', [AdminController::class, 'cargoDelete'])->name('cargo.delete');

    Route::get('/{module}', [AdminController::class, 'module'])
        ->name('module')
        ->whereIn('module', [
            'lantsa-routes', 'trip-schedule', 'passenger-manifest', 'cargo', 'notifications',
        ]);
});
