<?php

use App\Http\Controllers\Admin\BulkImportController;
use App\Http\Controllers\Admin\CannedResponseController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SlaPlanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Knowledge Base Public / Member Portal
Route::get('/kb', [KnowledgeBaseController::class, 'index'])->name('kb.index');
Route::get('/kb/{slug}', [KnowledgeBaseController::class, 'show'])->name('kb.show');
Route::post('/kb/{article}/vote', [KnowledgeBaseController::class, 'vote'])->name('kb.vote');

// Authenticated Application Area
Route::middleware('auth')->group(function () {
    // Dashboard & Profile
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');

    // Tickets (All authenticated roles)
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/rate', [TicketController::class, 'rate'])->name('tickets.rate');

    // Staff Tickets & KB actions (Agent & Admin)
    Route::middleware('role:admin,agent')->group(function () {
        Route::post('/tickets/{ticket}/note', [TicketController::class, 'internalNote'])->name('tickets.note');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
        Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::patch('/tickets/{ticket}/priority', [TicketController::class, 'updatePriority'])->name('tickets.priority');

        // KB Article Authoring
        Route::get('/kb-manage', [KnowledgeBaseController::class, 'manage'])->name('kb.manage');
        Route::get('/kb-manage/create', [KnowledgeBaseController::class, 'create'])->name('kb.create');
        Route::post('/kb-manage', [KnowledgeBaseController::class, 'store'])->name('kb.store');
        Route::get('/kb-manage/{article}/edit', [KnowledgeBaseController::class, 'edit'])->name('kb.edit');
        Route::put('/kb-manage/{article}', [KnowledgeBaseController::class, 'update'])->name('kb.update');
        Route::delete('/kb-manage/{article}', [KnowledgeBaseController::class, 'destroy'])->name('kb.destroy');
    });

    // Administrator Suite (Admin only)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Bulk Import Data Center
        Route::get('/bulk-import', [BulkImportController::class, 'index'])->name('bulk-import.index');
        Route::post('/bulk-import', [BulkImportController::class, 'import'])->name('bulk-import.import');
        Route::get('/bulk-import/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('bulk-import.template');

        // Analytics & Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

        // User Management
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

        // Departments Management
        Route::resource('departments', DepartmentController::class);

        // Categories Management
        Route::resource('categories', CategoryController::class);

        // SLA Policies Management
        Route::resource('sla', SlaPlanController::class);

        // Canned Responses Management
        Route::resource('canned', CannedResponseController::class);
    });
});
