<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HelpTicketController;
use App\Http\Controllers\ClassifiedController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ConnectionController;






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
// AI Assistant (site helper — distinct from the human OPEN CHAT above)
Route::post('/assistant/chat', [AssistantController::class, 'chat'])->name('assistant.chat');
// Connections (social graph: friends, requests, block, discover)
Route::get('/connections', [ConnectionController::class, 'index'])->name('pages.connections');
Route::post('/connections/request/{user}', [ConnectionController::class, 'request'])->name('connections.request');
Route::post('/connections/{connection}/accept', [ConnectionController::class, 'accept'])->name('connections.accept');
Route::delete('/connections/{connection}', [ConnectionController::class, 'destroy'])->name('connections.destroy');
Route::post('/connections/block/{user}', [ConnectionController::class, 'block'])->name('connections.block');
Route::delete('/connections/block/{user}', [ConnectionController::class, 'unblock'])->name('connections.unblock');
// Settings / Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('pages.profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// Events (calendar + RSVP)
Route::get('/events', [EventController::class, 'index'])->name('pages.events');
Route::post('/events', [EventController::class, 'store'])->name('events.store');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

// News
Route::get('/news', [NewsController::class, 'index'])->name('pages.news');
Route::post('/news', [NewsController::class, 'store'])->name('news.store');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');
Route::put('/news/{news}', [NewsController::class, 'update'])->name('news.update');
Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');
// Groups & Clubs
Route::get('/groups', [GroupController::class, 'index'])->name('pages.groups');
Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');
Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
// Media & Files
Route::get('/media', [MediaController::class, 'index'])->name('pages.media');
Route::post('/media', [MediaController::class, 'store'])->name('media.store');
Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');
Route::get('/media/{media}/download', [MediaController::class, 'download'])->name('media.download');
Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
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
