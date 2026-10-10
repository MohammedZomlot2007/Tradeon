<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BusinessRequestController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\IntelligenceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\SmartMatchController;
use App\Http\Controllers\SocialGraphController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing & Home Feed
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('feed');
    }

    return Inertia::render('Landing');
})->name('landing');

// SEO sitemap (static pages + latest companies; cached for 1h)
Route::get('/sitemap.xml', function () {
    $companies = \App\Models\Company::query()
        ->select(['slug', 'updated_at'])
        ->latest('updated_at')
        ->limit(500)
        ->get();
    $base = rtrim(config('app.url'), '/');
    $xml = view('sitemap', compact('companies', 'base'))->render();

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::get('/feed', [FeedController::class, 'index'])->middleware('subscribed')->name('feed');
Route::post('/posts', [FeedController::class, 'store'])->middleware(['auth', 'active', 'subscribed', 'storage.quota', 'throttle:30,1'])->name('posts.store');
Route::post('/posts/{id}/like', [FeedController::class, 'like'])->middleware(['auth', 'active', 'subscribed', 'throttle:60,1'])->name('posts.like');
Route::post('/posts/{id}/comment', [FeedController::class, 'comment'])->middleware(['auth', 'active', 'subscribed', 'throttle:60,1'])->name('posts.comment');
Route::middleware('auth')->group(function () {
    Route::post('/posts/{post}/save', [PostInteractionController::class, 'save'])->middleware('subscribed')->name('posts.save');
    Route::post('/posts/{post}/repost', [PostInteractionController::class, 'repost'])->middleware('subscribed')->name('posts.repost');
    Route::post('/posts/{post}/report', [PostInteractionController::class, 'report'])->middleware('subscribed')->name('posts.report');
});

// Directories
Route::get('/businesses', [CompanyController::class, 'index'])->name('businesses.index');
Route::get('/manufacturers', [CompanyController::class, 'manufacturers'])->name('manufacturers.index');
Route::get('/suppliers', [CompanyController::class, 'suppliers'])->name('suppliers.index');
Route::get('/company/{slug}', [CompanyController::class, 'show'])->name('companies.show');
Route::middleware('auth')->group(function () {
    Route::get('/onboarding/company', [CompanyController::class, 'onboarding'])->middleware('subscribed')->name('onboarding.company');
    Route::post('/onboarding/company', [CompanyController::class, 'storeOnboarding'])->middleware(['subscribed', 'storage.quota', 'throttle:10,1'])->name('onboarding.company.store');

    Route::post('/company/{id}/follow', [CompanyController::class, 'follow'])->middleware(['subscribed', 'throttle:60,1'])->name('companies.follow');
    Route::post('/company/{id}/connect', [CompanyController::class, 'connect'])->middleware(['subscribed', 'throttle:30,1'])->name('companies.connect');
    Route::post('/company/{id}/save', [CompanyController::class, 'save'])->middleware(['subscribed', 'throttle:60,1'])->name('companies.save');
});

// Explore & World Map
Route::get('/explore', [ExploreController::class, 'explore'])->name('explore');
Route::get('/search', [GlobalSearchController::class, 'index'])->name('search');
Route::get('/map', [ExploreController::class, 'worldMap'])->name('map');

// Business Requests & RFQ
Route::get('/requests', [BusinessRequestController::class, 'index'])->name('requests.index');
Route::post('/requests', [BusinessRequestController::class, 'storeRequest'])->middleware(['auth', 'active', 'storage.quota', 'throttle:20,1'])->name('requests.store');
Route::post('/rfq/{id}/quote', [BusinessRequestController::class, 'storeQuote'])->middleware(['auth', 'active', 'storage.quota', 'throttle:20,1'])->name('rfq.quote');

// Smart Match AI
Route::get('/smart-match', [SmartMatchController::class, 'index'])->name('smart-match.index');
Route::post('/api/smart-match', [SmartMatchController::class, 'match'])->middleware(['auth', 'subscribed', 'throttle:30,1'])->name('smart-match.match');

// Locale switching must work for guests too (landing, login, register)
Route::post('/locale', [LocaleController::class, 'update'])->middleware('throttle:60,1')->name('locale.update');

// Messenger
Route::middleware(['auth', 'active', 'subscribed'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->middleware('throttle:120,1')->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->middleware('throttle:30,1')->name('notifications.readAll');

    Route::post('/users/{user}/connect', [SocialGraphController::class, 'toggleUserConnection'])->middleware('throttle:30,1')->name('users.connect');
    Route::post('/users/{user}/block', [SocialGraphController::class, 'toggleBlock'])->middleware('throttle:30,1')->name('users.block');

    Route::get('/messages', [MessengerController::class, 'index'])->name('messages.index');
    Route::post('/messages/{conversationId}', [MessengerController::class, 'sendMessage'])->middleware('throttle:120,1')->name('messages.send');
    Route::post('/chat-requests', [MessengerController::class, 'sendChatRequest'])->middleware('throttle:20,1')->name('chat-requests.send');
    Route::post('/chat-requests/{id}/respond', [MessengerController::class, 'respondChatRequest'])->middleware('throttle:30,1')->name('chat-requests.respond');

    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verification', [VerificationController::class, 'store'])->middleware(['storage.quota', 'throttle:10,1'])->name('verification.store');
    Route::get('/verification/{verification}/document', [VerificationController::class, 'download'])->name('verification.document');

    Route::post('/factory', [FactoryController::class, 'store'])->middleware(['storage.quota', 'throttle:10,1'])->name('factory.store');
    Route::put('/factory/{factory}', [FactoryController::class, 'update'])->middleware(['storage.quota', 'throttle:20,1'])->name('factory.update');
    Route::delete('/factory/{factory}', [FactoryController::class, 'destroy'])->middleware('throttle:10,1')->name('factory.destroy');

    Route::post('/showcases', [ShowcaseController::class, 'store'])->middleware(['storage.quota', 'throttle:20,1'])->name('showcases.store');
    Route::put('/showcases/{showcase}', [ShowcaseController::class, 'update'])->middleware(['storage.quota', 'throttle:20,1'])->name('showcases.update');
    Route::delete('/showcases/{showcase}', [ShowcaseController::class, 'destroy'])->middleware('throttle:10,1')->name('showcases.destroy');

    // Company media uploads (logo, cover, post/showcase/factory images).
    // Bytes count against the company storage quota; over-quota uploads
    // send the owner to the pricing page to upgrade.
    Route::post('/media', [MediaController::class, 'store'])->middleware(['storage.quota', 'throttle:20,1'])->name('media.store');
    Route::delete('/media/{mediaUpload}', [MediaController::class, 'destroy'])->middleware('throttle:20,1')->name('media.destroy');
});

// Communities & Events
Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/communities/{slug}', [CommunityController::class, 'show'])->name('communities.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');

// Product Showcase / Catalog
Route::middleware(['auth', 'active', 'subscribed', 'storage.quota'])->group(function () {
    Route::resource('products', ProductController::class);
});

// Intelligence & Network CRM & Dashboard
Route::get('/intelligence', [IntelligenceController::class, 'index'])->middleware('subscribed')->name('intelligence.index');
Route::get('/network', [NetworkController::class, 'index'])->middleware(['auth', 'subscribed'])->name('network.index');
Route::get('/welcome', [WelcomeController::class, 'show'])->middleware(['auth', 'subscribed'])->name('welcome');
Route::get('/biz-dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'subscribed'])->name('dashboard');

Route::get('/pricing', [BillingController::class, 'pricing'])->name('billing.pricing');
Route::post('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->middleware(['auth', 'active', 'throttle:10,1'])->name('billing.checkout');
Route::middleware('auth')->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
});
Route::post('/billing/webhooks/paddle', [BillingController::class, 'webhook'])->name('billing.webhooks.paddle');

// Admin Platform
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/verifications/{id}/approve', [AdminController::class, 'approveVerification'])->name('admin.verifications.approve');
    Route::post('/admin/verifications/{id}/reject', [AdminController::class, 'rejectVerification'])->name('admin.verifications.reject');
    Route::post('/admin/users/{user}/suspension', [AdminController::class, 'toggleUserSuspension'])->name('admin.users.suspension');
    Route::post('/admin/reports/{report}', [AdminController::class, 'updateReport'])->name('admin.reports.update');
});

// Standard User Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:30,1')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:10,1')->name('profile.destroy');
});

require __DIR__.'/auth.php';
