<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HelpTicketController;
use App\Http\Controllers\ClassifiedController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;






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
    
// Open Chat (all-campus live stream) — the dashboard/landing page
Route::get('/dashboard', [ChatController::class, 'index'])->name('pages.dashboard');
Route::get('/chat/messages', [ChatController::class, 'fetch'])->name('chat.fetch');
Route::post('/chat/messages', [ChatController::class, 'store'])->name('chat.store');
Route::post('/chat/messages/{message}/react', [ChatController::class, 'react'])->name('chat.react');
Route::get('/messages', [PageController::class, 'messages'])->name('pages.messages');
Route::get('/connections', [PageController::class, 'connections'])->name('pages.connections'); 
// Settings / Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('pages.profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
Route::get('/events', [PageController::class, 'events'])->name('pages.events');
Route::get('/groups', [PageController::class, 'groups'])->name('pages.groups');
Route::get('/media', [PageController::class, 'media'])->name('pages.media');
// Classifieds / Roommates
Route::get('/classifieds', [ClassifiedController::class, 'index'])->name('pages.classifieds');
Route::post('/classifieds', [ClassifiedController::class, 'store'])->name('classifieds.store');
Route::put('/classifieds/{classified}', [ClassifiedController::class, 'update'])->name('classifieds.update');
Route::delete('/classifieds/{classified}', [ClassifiedController::class, 'destroy'])->name('classifieds.destroy');
Route::post('/classifieds/{classified}/replies', [ClassifiedController::class, 'storeReply'])->name('classifieds.replies.store');
Route::delete('/classified-replies/{reply}', [ClassifiedController::class, 'destroyReply'])->name('classifieds.replies.destroy');
    
    
Route::get('/change-password',  [PageController::class, 'showChangePassword'])->name('pages.change-password');
Route::post('/change-password', [PageController::class, 'changePassword'])->name('pages.change-password.update');


// Help Ticket routes
Route::get('/helpticket', [HelpTicketController::class, 'show'])->name('help.ticket');
Route::post('/helpticket', [HelpTicketController::class, 'submit'])->name('help.ticket.submit');


Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');

});
