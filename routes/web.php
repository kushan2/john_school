<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HelpTicketController;






// ── Redirect root to dashboard or login ──────────────────────────────────────
Route::get('/', fn() => redirect()->route('pages.dashboard'));



// ── Guest routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {


Route::get('/landing', [PageController::class, 'landing'])->name('landing');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');




Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('user.login');

Route::get('/register',  [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('user.register');





Route::get('/forgot-password',  [AuthController::class, 'showForgot'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');
Route::get('/reset-password/{token}',  [AuthController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password',         [AuthController::class, 'resetPassword'])->name('password.update');

});




// ── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('pages.dashboard');
Route::get('/messages', [PageController::class, 'messages'])->name('pages.messages');
Route::get('/connections', [PageController::class, 'connections'])->name('pages.connections'); 
Route::get('/profile', [PageController::class, 'profile'])->name('pages.profile');
Route::get('/events', [PageController::class, 'events'])->name('pages.events');
Route::get('/groups', [PageController::class, 'groups'])->name('pages.groups');
Route::get('/media', [PageController::class, 'media'])->name('pages.media');
Route::get('/classifieds', [PageController::class, 'classifieds'])->name('pages.classifieds');
    
    
Route::get('/change-password',  [PageController::class, 'showChangePassword'])->name('pages.change-password');
Route::post('/change-password', [PageController::class, 'changePassword'])->name('pages.change-password.update');


// Help Ticket routes
Route::get('/helpticket', [HelpTicketController::class, 'show'])->name('help.ticket');
Route::post('/helpticket', [HelpTicketController::class, 'submit'])->name('help.ticket.submit');


Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');

});
