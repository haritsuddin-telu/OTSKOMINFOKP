<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserTableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OTSController;
use App\Http\Controllers\RolePermissionController;
use App\Models\Secret;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});





Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'permission:view dashboard',
])->group(function () {
    Route::get('/dashboard', function () {
        $jumlahUser = User::count();
        $jumlahOts = Secret::count();
        return view('dashboard', compact('jumlahUser', 'jumlahOts'));
    })->name('dashboard');
});



// Route input & manajemen (khusus pegawai, harus login)
Route::middleware(['auth','permission:access ots'])->group(function () {
    Route::get('/ots', [OTSController::class, 'form'])->name('ots.form');
    Route::post('/ots', [OTSController::class, 'store'])->name('ots.store');
    Route::get('/ots/my-secrets', [OTSController::class, 'index'])->name('ots.index');
    Route::get('/ots/stats', [OTSController::class, 'stats'])->name('ots.stats');
    Route::delete('/ots/{secret}', [OTSController::class, 'destroy'])->name('ots.destroy');
    Route::delete('/ots/cleanup', [OTSController::class, 'cleanup'])->name('ots.cleanup');
    Route::get('/ots/chart', [OTSController::class, 'chart'])->name('ots.chart');
// bracket penutup dihapus agar tidak error
});

// Route display (publik, tanpa login, view OTS_display)
Route::get('/secret/{slug}', [OTSController::class, 'show'])->name('ots.show')->middleware('signed');
Route::get('/secret/{slug}/download', [OTSController::class, 'download'])->name('ots.download')->middleware('signed');
Route::get('/secret/{slug}/info', [OTSController::class, 'info'])->name('ots.info');




// Role & Permission Management (berbasis permission)

// Role & Permission Management (khusus manage role permission)
Route::middleware(['auth', 'permission:manage role permission'])->group(function () {
    Route::get('/role-permission', [RolePermissionController::class, 'index'])->name('role.permission');
    Route::post('/roles', [RolePermissionController::class, 'storeRole'])->name('roles.store');
    Route::delete('/roles/{id}', [RolePermissionController::class, 'destroyRole'])->name('roles.destroy');
    Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('permissions.store');
    Route::delete('/permissions/{id}', [RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');
    Route::post('/roles/assign-permission', [RolePermissionController::class, 'assignPermissionToRole'])->name('roles.assign_permission');
    Route::post('/users/assign-role', [RolePermissionController::class, 'assignRoleToUser'])->name('users.assign_role');
});

// Table (view data)
Route::middleware(['auth', 'permission:view data'])->group(function () {
    Route::get('/table', [UserTableController::class, 'index'])->name('table');
    Route::get('/users', [UserTableController::class, 'index'])->name('users.index');
});

// User create (create data)
Route::middleware(['auth', 'permission:create data'])->group(function () {
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
});

// User edit (edit data)
Route::middleware(['auth', 'permission:edit data'])->group(function () {
    Route::get('/users/{id}/edit', [UserTableController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserTableController::class, 'update'])->name('users.update');
});

// User delete (delete data)
Route::middleware(['auth', 'permission:delete data'])->group(function () {
    Route::delete('/users/{id}', [UserTableController::class, 'destroy'])->name('users.destroy');
});

// Log Activity (khusus permission view log)
Route::middleware(['auth', 'permission:view log'])->group(function () {
    Route::get('/log', function () {
        $secrets = Secret::with('user')->get();
        return view('log', compact('secrets'));
    })->name('log');
});

// All protected routes already use 'auth:sanctum', config('jetstream.auth_session'), and 'verified' middleware.
// This ensures users are redirected to login if not authenticated.
// For extra clarity, add a fallback route for unauthorized access:

Route::fallback(function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    abort(404);
});
