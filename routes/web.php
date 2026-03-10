<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ParticipantFormController;

// Public routes
Route::get('/', [ParticipantFormController::class, 'index'])->name('form.index');
Route::post('/submit-form', [ParticipantFormController::class, 'submit'])->name('form.submit');
Route::get('/status/{id}', [ParticipantFormController::class, 'getStatus'])->name('form.status');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    // Route::get('/setup-admin', [AuthController::class, 'createFirstAdmin'])->name('setup.admin');
    // Route::post('/setup-admin', [AuthController::class, 'createFirstAdmin'])->name('setup.admin.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Participants management
    Route::get('/participants', [AdminController::class, 'participants'])->name('participants');
    Route::get('/participants/{id}', [AdminController::class, 'viewParticipant'])->name('participant.view');
    Route::get('/participants/{id}/edit', [AdminController::class, 'editParticipant'])->name('participant.edit');
    Route::patch('/participants/{id}', [AdminController::class, 'updateParticipant'])->name('participant.update');
    Route::post('/participants/{id}/approve', [AdminController::class, 'approveParticipant'])->name('participant.approve');
    Route::post('/participants/{id}/reject', [AdminController::class, 'rejectParticipant'])->name('participant.reject');
    Route::delete('/participants/{id}', [AdminController::class, 'deleteParticipant'])->name('participant.delete');
    Route::get('/participants/{participantId}/preview/{fileType}', [AdminController::class, 'previewFile'])->name('participant.preview');
    Route::get('/participants/{participantId}/download/{fileType}', [AdminController::class, 'downloadFile'])->name('participant.download');
    Route::get('/export', [AdminController::class, 'exportParticipants'])->name('export');

    // Categories management
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'createCategory'])->name('category.create');
    Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])->name('category.delete');
    
    // Users management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'createUser'])->name('user.create');
    Route::patch('/users/{id}', [AdminController::class, 'editUser'])->name('user.edit');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('user.delete');
});
