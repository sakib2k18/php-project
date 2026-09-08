<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SuccessStoryController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/team', [PageController::class, 'team'])->name('team');
Route::get('/get-involved', [PageController::class, 'getInvolved'])->name('get-involved');
Route::get('/search', SearchController::class)->name('search');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');

Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])
    ->middleware('remember.campaign')   // stores a "recently viewed" cookie
    ->name('campaigns.show');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::get('/stories', [SuccessStoryController::class, 'index'])->name('stories.index');
Route::get('/stories/{story}', [SuccessStoryController::class, 'show'])->name('stories.show');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

// Server-side proxy for the weather widget's refresh button.
Route::get('/api/weather', WeatherController::class)
    ->middleware('throttle:weather')
    ->name('api.weather');

/*
|--------------------------------------------------------------------------
| Guest-only authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Signed-in supporters
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/dashboard/donations', [DonationController::class, 'index'])->name('donations.index');
    Route::get('/donate', [DonationController::class, 'create'])->name('donations.create');
    Route::post('/donate', [DonationController::class, 'store'])
        ->middleware('throttle:donations')
        ->name('donations.store');
    Route::get('/dashboard/donations/{donation}', [DonationController::class, 'show'])->name('donations.show');
    Route::get('/dashboard/donations/{donation}/receipt', [DonationController::class, 'receipt'])->name('donations.receipt');

    Route::get('/volunteer', [VolunteerController::class, 'index'])->name('volunteer.index');
    Route::post('/volunteer', [VolunteerController::class, 'store'])->name('volunteer.store');
    Route::put('/volunteer/{volunteer}', [VolunteerController::class, 'update'])->name('volunteer.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

/*
|--------------------------------------------------------------------------
| Administration panel — auth + admin middleware, enforced server-side
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Campaigns
        Route::resource('campaigns', Admin\CampaignController::class)->except(['destroy']);
        Route::delete('campaigns/{campaign}', [Admin\CampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::post('campaigns/{id}/restore', [Admin\CampaignController::class, 'restore'])->name('campaigns.restore');
        Route::post('campaigns/{campaign}/featured', [Admin\CampaignController::class, 'toggleFeatured'])->name('campaigns.featured');
        Route::post('campaigns/{campaign}/updates', [Admin\CampaignUpdateController::class, 'store'])->name('campaigns.updates.store');
        Route::delete('campaigns/{campaign}/updates/{update}', [Admin\CampaignUpdateController::class, 'destroy'])->name('campaigns.updates.destroy');

        // Donations
        Route::get('donations', [Admin\DonationController::class, 'index'])->name('donations.index');
        Route::get('donations/reports', Admin\DonationReportController::class)->name('donations.reports');
        Route::post('donations/recalculate', [Admin\DonationController::class, 'recalculate'])->name('donations.recalculate');
        Route::get('donations/{donation}', [Admin\DonationController::class, 'show'])->name('donations.show');
        Route::post('donations/{donation}/review', [Admin\DonationController::class, 'review'])->name('donations.review');
        Route::delete('donations/{donation}', [Admin\DonationController::class, 'destroy'])->name('donations.destroy');

        // Projects
        Route::resource('projects', Admin\ProjectController::class)->except(['show']);
        Route::post('projects/{id}/restore', [Admin\ProjectController::class, 'restore'])->name('projects.restore');
        Route::post('projects/{project}/publish', [Admin\ProjectController::class, 'togglePublished'])->name('projects.publish');

        // Events
        Route::resource('events', Admin\EventController::class)->except(['show']);
        Route::post('events/{id}/restore', [Admin\EventController::class, 'restore'])->name('events.restore');

        // Success stories
        Route::resource('stories', Admin\SuccessStoryController::class)
            ->parameters(['stories' => 'story'])
            ->except(['show']);
        Route::post('stories/{id}/restore', [Admin\SuccessStoryController::class, 'restore'])->name('stories.restore');

        // Announcements
        Route::resource('announcements', Admin\AnnouncementController::class)->except(['show']);

        // Gallery
        Route::resource('gallery', Admin\GalleryController::class)
            ->parameters(['gallery' => 'gallery'])
            ->except(['show']);

        // Team members
        Route::resource('team', Admin\TeamMemberController::class)
            ->parameters(['team' => 'team_member'])
            ->except(['show']);

        // Volunteers
        Route::get('volunteers', [Admin\VolunteerController::class, 'index'])->name('volunteers.index');
        Route::get('volunteers/{volunteer}', [Admin\VolunteerController::class, 'show'])->name('volunteers.show');
        Route::post('volunteers/{volunteer}/review', [Admin\VolunteerController::class, 'review'])->name('volunteers.review');
        Route::delete('volunteers/{volunteer}', [Admin\VolunteerController::class, 'destroy'])->name('volunteers.destroy');

        // Users
        Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Contact messages
        Route::get('messages', [Admin\ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Admin\ContactMessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{message}/toggle-read', [Admin\ContactMessageController::class, 'toggleRead'])->name('messages.toggle-read');
        Route::delete('messages/{message}', [Admin\ContactMessageController::class, 'destroy'])->name('messages.destroy');

        // Activity log
        Route::get('activity', [Admin\ActivityLogController::class, 'index'])->name('activity.index');

        // Settings & admin account
        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::get('password', [Admin\ProfileController::class, 'editPassword'])->name('password.edit');
    });
